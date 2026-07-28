function isSelectableMedicine(medicine) {
    return (medicine.quantity_remaining ?? 0) > 0 && medicine.expiry_status !== 'expired';
}

export function medicineRecommendationsPanel({ storeUrl, destroyUrlTemplate, medicines, initialItems }) {
    return {
        medicines,
        items: initialItems,
        query: '',
        selectedId: '',
        quantity: '',
        instructions: '',
        open: false,
        saving: false,
        statusText: '',
        statusClass: 'text-slate-400',
        get filtered() {
            const q = this.query.trim().toLowerCase();
            const list = this.medicines.filter(isSelectableMedicine);

            if (!q) {
                return list.slice(0, 40);
            }

            return list
                .filter((m) => {
                    const hay = `${m.label} ${m.generic_name} ${m.brand_name} ${m.dosage_strength || ''}`.toLowerCase();
                    return hay.includes(q);
                })
                .slice(0, 40);
        },
        selectMedicine(medicine) {
            this.selectedId = String(medicine.id);
            this.query = medicine.label;
            this.open = false;
        },
        expiryClass(status) {
            if (status === 'expired') return 'text-red-800 font-semibold';
            if (status === 'current') return 'text-red-600';
            if (status === 'soon') return 'text-amber-700';
            return 'text-slate-500';
        },
        async add() {
            if (!this.selectedId) {
                this.statusText = 'Select a medicine first.';
                this.statusClass = 'text-rose-600';
                return;
            }

            this.saving = true;
            this.statusText = 'Saving…';
            this.statusClass = 'text-amber-600';

            try {
                const response = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        medicine_id: Number(this.selectedId),
                        quantity: this.quantity === '' ? null : Number(this.quantity),
                        instructions: this.instructions || null,
                    }),
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Failed');
                }

                this.items.push(data.item);
                this.selectedId = '';
                this.query = '';
                this.quantity = '';
                this.instructions = '';
                this.statusText = 'Added to prescription';
                this.statusClass = 'text-teal-700';
            } catch (e) {
                this.statusText = e.message || 'Error saving';
                this.statusClass = 'text-rose-600';
            } finally {
                this.saving = false;
            }
        },
        async remove(item) {
            if (!confirm('Remove this medicine from the prescription?')) {
                return;
            }

            this.saving = true;
            try {
                const url = destroyUrlTemplate.replace('__ID__', String(item.id));
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed');
                }

                this.items = this.items.filter((row) => row.id !== item.id);
                this.statusText = 'Removed';
                this.statusClass = 'text-teal-700';
            } catch (e) {
                this.statusText = e.message || 'Error removing';
                this.statusClass = 'text-rose-600';
            } finally {
                this.saving = false;
            }
        },
    };
}

export function medicineDispensePanel({
    storeUrl,
    destroyUrlTemplate,
    medicines,
    initialItems,
    recommendations = [],
}) {
    return {
        medicines,
        items: initialItems,
        recommendations,
        query: '',
        selectedId: '',
        quantity: 1,
        remarks: '',
        open: false,
        saving: false,
        statusText: '',
        statusClass: 'text-slate-400',
        get filtered() {
            const q = this.query.trim().toLowerCase();
            const list = this.medicines.filter(isSelectableMedicine);

            if (!q) {
                return list.slice(0, 40);
            }

            return list
                .filter((m) => {
                    const hay = `${m.label} ${m.generic_name} ${m.brand_name} ${m.dosage_strength || ''}`.toLowerCase();
                    return hay.includes(q);
                })
                .slice(0, 40);
        },
        selectedMedicine() {
            return this.medicines.find((m) => String(m.id) === String(this.selectedId)) || null;
        },
        medicineForRecommendation(rec) {
            const medicine = this.medicines.find((m) => String(m.id) === String(rec.medicine_id)) || null;
            if (!medicine || !isSelectableMedicine(medicine)) {
                return null;
            }

            return medicine;
        },
        isRecommendationDispensed(rec) {
            return this.items.some((item) => String(item.medicine_id) === String(rec.medicine_id));
        },
        selectMedicine(medicine) {
            this.selectedId = String(medicine.id);
            this.query = medicine.label;
            this.open = false;
        },
        selectRecommendation(rec) {
            const medicine = this.medicineForRecommendation(rec);
            if (!medicine) {
                this.statusText = 'Medicine is out of stock or expired.';
                this.statusClass = 'text-rose-600';
                return;
            }

            this.selectedId = String(medicine.id);
            this.query = medicine.label;
            this.quantity = rec.quantity && Number(rec.quantity) > 0 ? Number(rec.quantity) : 1;
            this.remarks = rec.instructions || '';
            this.open = false;
            this.statusText = 'Prescription loaded — adjust qty if needed, then Dispense.';
            this.statusClass = 'text-teal-700';
        },
        expiryClass(status) {
            if (status === 'expired') return 'text-red-800 font-semibold';
            if (status === 'current') return 'text-red-600';
            if (status === 'soon') return 'text-amber-700';
            return 'text-slate-500';
        },
        patchMedicine(updated) {
            if (!updated) {
                return;
            }

            this.medicines = this.medicines.map((m) => (m.id === updated.id ? { ...m, ...updated } : m));
        },
        async dispenseMedicine(medicineId, qty, remarks, { clearForm = false } = {}) {
            this.saving = true;
            this.statusText = 'Saving…';
            this.statusClass = 'text-amber-600';

            try {
                const response = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        medicine_id: Number(medicineId),
                        quantity: qty,
                        remarks: remarks || null,
                    }),
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || Object.values(data.errors || {}).flat()[0] || 'Failed');
                }

                this.items.push(data.item);
                this.patchMedicine(data.medicine);

                if (clearForm) {
                    this.selectedId = '';
                    this.query = '';
                    this.quantity = 1;
                    this.remarks = '';
                }

                this.statusText = 'Medicine dispensed';
                this.statusClass = 'text-teal-700';
                return true;
            } catch (e) {
                this.statusText = e.message || 'Error saving';
                this.statusClass = 'text-rose-600';
                return false;
            } finally {
                this.saving = false;
            }
        },
        async add() {
            const medicine = this.selectedMedicine();
            if (!medicine) {
                this.statusText = 'Select a medicine first.';
                this.statusClass = 'text-rose-600';
                return;
            }

            if (!isSelectableMedicine(medicine)) {
                this.statusText = 'Medicine is out of stock or expired.';
                this.statusClass = 'text-rose-600';
                return;
            }

            const qty = Number(this.quantity);
            if (!Number.isFinite(qty) || qty < 1) {
                this.statusText = 'Enter a valid quantity.';
                this.statusClass = 'text-rose-600';
                return;
            }

            await this.dispenseMedicine(medicine.id, qty, this.remarks, { clearForm: true });
        },
        async dispenseRecommendation(rec) {
            const medicine = this.medicineForRecommendation(rec);
            if (!medicine) {
                this.statusText = 'Medicine is out of stock or expired.';
                this.statusClass = 'text-rose-600';
                return;
            }

            if (medicine.quantity_remaining < 1) {
                this.statusText = 'Out of stock for this medicine.';
                this.statusClass = 'text-rose-600';
                return;
            }

            let qty = rec.quantity && Number(rec.quantity) > 0 ? Number(rec.quantity) : 1;
            if (qty > medicine.quantity_remaining) {
                this.statusText = `Only ${medicine.quantity_remaining} remaining in stock.`;
                this.statusClass = 'text-rose-600';
                this.selectRecommendation(rec);
                return;
            }

            await this.dispenseMedicine(medicine.id, qty, rec.instructions || null);
        },
        async remove(item) {
            if (!confirm('Remove this dispense and return stock?')) {
                return;
            }

            this.saving = true;
            try {
                const url = destroyUrlTemplate.replace('__ID__', String(item.id));
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Failed');
                }

                this.items = this.items.filter((row) => row.id !== item.id);
                this.patchMedicine(data.medicine);
                this.statusText = 'Dispense removed; stock restored';
                this.statusClass = 'text-teal-700';
            } catch (e) {
                this.statusText = e.message || 'Error removing';
                this.statusClass = 'text-rose-600';
            } finally {
                this.saving = false;
            }
        },
    };
}
