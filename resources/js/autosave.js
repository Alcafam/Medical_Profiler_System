export function autosaveField({
    url,
    initialValue = '',
    initialVersion = 0,
    stationId = null,
    debounceMs = 700,
}) {
    return {
        value: initialValue ?? '',
        version: initialVersion ?? 0,
        status: 'idle',
        lastEditor: null,
        lastSavedAt: null,
        conflict: null,
        timer: null,
        stationId,

        queueSave() {
            this.status = 'pending';
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.save(false), debounceMs);
        },

        async save(force = false) {
            this.status = 'saving';
            this.conflict = null;

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        value: this.value,
                        version: this.version,
                        force,
                        station_id: this.stationId,
                    }),
                });

                const data = await response.json();

                if (response.status === 409) {
                    this.status = 'conflict';
                    this.conflict = data.conflict;
                    return;
                }

                if (!response.ok) {
                    this.status = 'error';
                    return;
                }

                this.version = data.version;
                this.value = data.value ?? '';
                this.lastEditor = data.updated_by;
                this.lastSavedAt = data.updated_at;
                this.status = 'saved';
            } catch (error) {
                this.status = 'error';
            }
        },

        keepTheirs() {
            if (!this.conflict) return;
            this.value = this.conflict.current_value ?? '';
            this.version = this.conflict.current_version;
            this.lastEditor = this.conflict.updated_by ?? this.lastEditor;
            this.lastSavedAt = this.conflict.updated_at ?? this.lastSavedAt;
            this.conflict = null;
            this.status = 'saved';
        },

        overwriteMine() {
            this.save(true);
        },
    };
}
