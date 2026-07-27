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
