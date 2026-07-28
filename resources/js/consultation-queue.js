import Alpine from 'alpinejs';

export function consultationQueueToggle({ url, initial, locked }) {
    return {
        queued: initial,
        locked: locked,
        saving: false,
        statusText: '',
        statusClass: 'text-slate-400',
        async save() {
            if (this.locked) {
                return;
            }

            this.saving = true;
            this.statusText = 'Saving…';
            this.statusClass = 'text-amber-600';

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ queued: this.queued }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.queued = !this.queued;
                    throw new Error(data.message || 'Failed');
                }

                this.statusText = this.queued ? 'Added to consultation queue' : 'Removed from queue';
                this.statusClass = 'text-teal-700';
            } catch (e) {
                this.statusText = e.message || 'Error saving';
                this.statusClass = 'text-rose-600';
            } finally {
                this.saving = false;
            }
        },
    };
}

export function consultationDisposition({ url, initial, reloadOnChange = true }) {
    return {
        disposition: initial,
        saving: false,
        statusText: '',
        statusClass: 'text-slate-400',
        async save() {
            this.saving = true;
            this.statusText = 'Saving…';
            this.statusClass = 'text-amber-600';

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ disposition: this.disposition }),
                });

                if (!response.ok) {
                    throw new Error('Failed');
                }

                this.statusText = 'Saved';
                this.statusClass = 'text-teal-700';

                if (!reloadOnChange) {
                    return;
                }

                const tab = new URLSearchParams(window.location.search).get('tab');
                const shouldReload =
                    (this.disposition !== 'active' && tab !== 'completed') ||
                    (this.disposition === 'active' && tab === 'completed');

                if (shouldReload) {
                    setTimeout(() => window.location.reload(), 400);
                }
            } catch (e) {
                this.statusText = 'Error saving';
                this.statusClass = 'text-rose-600';
            } finally {
                this.saving = false;
            }
        },
    };
}

function ensureConsultationLocksStore(seed = {}) {
    if (!Alpine.store('consultationLocks')) {
        Alpine.store('consultationLocks', {
            locksByVisit: {},
            lockFor(visitId) {
                return this.locksByVisit[String(visitId)] || null;
            },
            isLockedByOther(visitId) {
                const lock = this.lockFor(visitId);
                return Boolean(lock && !lock.is_mine);
            },
            isLockedByMe(visitId) {
                const lock = this.lockFor(visitId);
                return Boolean(lock && lock.is_mine);
            },
            replaceLocks(map) {
                this.locksByVisit = map;
            },
        });
    }

    Alpine.store('consultationLocks').replaceLocks(seed);
}

export function consultationQueueLocks({ pollUrl, currentUserId, initialLocks = [], intervalMs = 4000 }) {
    const seed = {};
    (initialLocks || []).forEach((lock) => {
        if (lock?.visit_id != null) {
            seed[String(lock.visit_id)] = lock;
        }
    });

    ensureConsultationLocksStore(seed);

    return {
        currentUserId,
        timer: null,
        init() {
            this.refresh();
            this.timer = setInterval(() => this.refresh(), intervalMs);
        },
        destroy() {
            if (this.timer) {
                clearInterval(this.timer);
            }
        },
        async refresh() {
            try {
                const response = await fetch(pollUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const map = {};
                (data.locks || []).forEach((lock) => {
                    map[String(lock.visit_id)] = lock;
                });
                Alpine.store('consultationLocks').replaceLocks(map);
            } catch (e) {
                // Keep last known locks on transient network errors.
            }
        },
    };
}

export function startConsultationLockHeartbeat({ heartbeatUrl, releaseUrl, intervalMs = 25000 }) {
    if (!heartbeatUrl || !releaseUrl) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let stopped = false;

    const beat = async () => {
        if (stopped) {
            return;
        }

        try {
            const response = await fetch(heartbeatUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (response.status === 423) {
                stopped = true;
                window.location.href = '/clients';
            }
        } catch (e) {
            // Ignore transient errors; next beat will retry.
        }
    };

    const release = () => {
        if (stopped) {
            return;
        }

        stopped = true;

        fetch(releaseUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '{}',
            credentials: 'same-origin',
            keepalive: true,
        }).catch(() => {});
    };

    beat();
    const timer = setInterval(beat, intervalMs);

    window.addEventListener('pagehide', release);
    window.addEventListener('beforeunload', release);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !stopped) {
            beat();
        }
    });

    return () => {
        clearInterval(timer);
        release();
    };
}
