import './bootstrap';
import $ from 'jquery';
import Alpine from 'alpinejs';
import { autosaveField } from './autosave';
import { consultationDisposition, consultationQueueToggle } from './consultation-queue';
import { medicineDispensePanel, medicineRecommendationsPanel } from './medicines';

window.$ = window.jQuery = $;
window.Alpine = Alpine;
window.autosaveField = autosaveField;
window.consultationQueueToggle = consultationQueueToggle;
window.consultationDisposition = consultationDisposition;
window.medicineRecommendationsPanel = medicineRecommendationsPanel;
window.medicineDispensePanel = medicineDispensePanel;

Alpine.start();

function asianBmiCategory(bmi) {
    if (bmi < 18.5) return 'Underweight';
    if (bmi < 23) return 'Normal';
    if (bmi < 25) return 'Overweight';
    if (bmi < 30) return 'Obese I';
    return 'Obese II';
}

const BMI_CATEGORY_CLASSES = [
    'bmi-cat-underweight',
    'bmi-cat-normal',
    'bmi-cat-overweight',
    'bmi-cat-obese-i',
    'bmi-cat-obese-ii',
    'bmi-cat-empty',
];

function bmiCategoryClass(category) {
    switch (category) {
        case 'Underweight':
            return 'bmi-cat-underweight';
        case 'Normal':
            return 'bmi-cat-normal';
        case 'Overweight':
            return 'bmi-cat-overweight';
        case 'Obese I':
            return 'bmi-cat-obese-i';
        case 'Obese II':
            return 'bmi-cat-obese-ii';
        default:
            return 'bmi-cat-empty';
    }
}

function updateBmiPanel() {
    const $panel = $('.js-bmi-panel');
    if (!$panel.length) return;

    const height = Number.parseFloat($('[data-field-slug="height_cm"] input, [data-field-slug="height_cm"] textarea, [data-field-slug="height_cm"] select').val());
    const weight = Number.parseFloat($('[data-field-slug="weight_kg"] input, [data-field-slug="weight_kg"] textarea, [data-field-slug="weight_kg"] select').val());
    const $category = $panel.find('.js-bmi-category');
    const $categoryCol = $category.closest('[class*="col-"]');

    if (!Number.isFinite(height) || !Number.isFinite(weight) || height <= 0 || weight <= 0) {
        $panel.find('.js-bmi-value').text('—');
        $category.text('—');
        $categoryCol.removeClass(BMI_CATEGORY_CLASSES.join(' ')).addClass('bmi-cat-empty');
        return;
    }

    const heightM = height / 100;
    const bmi = Math.round((weight / (heightM * heightM)) * 10) / 10;
    const category = asianBmiCategory(bmi);

    $panel.find('.js-bmi-value').text(bmi.toFixed(1));
    $category.text(category);
    $categoryCol
        .removeClass(BMI_CATEGORY_CLASSES.join(' '))
        .addClass(bmiCategoryClass(category));
}

function bpCategoryFromValues(systolic, diastolic) {
    const sys = Number.parseFloat(systolic);
    const dia = Number.parseFloat(diastolic);

    if (!Number.isFinite(sys) || !Number.isFinite(dia)) {
        return null;
    }

    const systolicTier = (() => {
        if (sys >= 180) return 4;
        if (sys >= 160) return 3;
        if (sys >= 140) return 2;
        if (sys >= 121) return 1;
        return 0; // <= 120
    })();

    const diastolicTier = (() => {
        if (dia >= 110) return 4;
        if (dia >= 100) return 3;
        if (dia >= 90) return 2;
        if (dia >= 81) return 1;
        return 0; // <= 80
    })();

    switch (Math.max(systolicTier, diastolicTier)) {
        case 0:
            return 'Normal';
        case 1:
            return 'Elevated';
        case 2:
            return 'Hypertension Stage 1';
        case 3:
            return 'Hypertension Stage 2';
        case 4:
            return 'Hypertensive Crisis';
        default:
            return null;
    }
}

const BP_CATEGORY_CLASSES = [
    'bp-cat-normal',
    'bp-cat-elevated',
    'bp-cat-stage-1',
    'bp-cat-stage-2',
    'bp-cat-crisis',
    'bp-cat-empty',
];

function bpCategoryClass(category) {
    switch (category) {
        case 'Normal':
            return 'bp-cat-normal';
        case 'Elevated':
            return 'bp-cat-elevated';
        case 'Hypertension Stage 1':
            return 'bp-cat-stage-1';
        case 'Hypertension Stage 2':
            return 'bp-cat-stage-2';
        case 'Hypertensive Crisis':
            return 'bp-cat-crisis';
        default:
            return 'bp-cat-empty';
    }
}

function updateBloodPressurePanel() {
    const $panel = $('.js-bp-panel');
    if (!$panel.length) return;

    const systolic = String($('[data-field-slug="systolic"] input, [data-field-slug="systolic"] textarea').val() ?? '').trim();
    const diastolic = String($('[data-field-slug="diastolic"] input, [data-field-slug="diastolic"] textarea').val() ?? '').trim();
    const $category = $panel.find('.js-bp-category');
    const $categoryCol = $category.closest('[class*="col-"]');

    if (systolic === '' || diastolic === '') {
        $panel.find('.js-bp-value').text('—');
        $category.text('—');
        $categoryCol.removeClass(BP_CATEGORY_CLASSES.join(' ')).addClass('bp-cat-empty');
        return;
    }

    const category = bpCategoryFromValues(systolic, diastolic);

    $panel.find('.js-bp-value').text(`${systolic}/${diastolic}`);
    $category.text(category ?? '—');
    $categoryCol
        .removeClass(BP_CATEGORY_CLASSES.join(' '))
        .addClass(bpCategoryClass(category));
}

function fieldInputValue(slug) {
    const $field = $(`[data-field-slug="${slug}"]`);
    if (!$field.length) {
        return null;
    }

    return String($field.find('input, textarea, select').first().val() ?? '').trim();
}

function previewDisplayValue(value) {
    return value === null || value === '' ? '—' : value;
}

function ageFromDob(dob) {
    if (!dob) {
        return null;
    }

    const date = new Date(dob);
    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const today = new Date();
    let age = today.getFullYear() - date.getFullYear();
    const monthDiff = today.getMonth() - date.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) {
        age -= 1;
    }

    return age >= 0 ? age : null;
}

function buildPreviewFullName() {
    const last = fieldInputValue('last_name');
    const first = fieldInputValue('first_name');
    const department = fieldInputValue('department');
    const clientType = fieldInputValue('client_type');

    let name = '—';
    if (last && first) {
        name = `${last}, ${first}`;
    } else if (last) {
        name = last;
    } else if (first) {
        name = first;
    }

    const meta = [department, clientType].filter(Boolean);
    if (name !== '—' && meta.length) {
        name += ` (${meta.join(' - ')})`;
    }

    return name;
}

function updateCurrentVisitPreview() {
    const $preview = $('.js-current-visit-preview');
    if (!$preview.length) {
        return;
    }

    $('[data-field-slug]').each(function () {
        const slug = $(this).attr('data-field-slug');
        const raw = String($(this).find('input, textarea, select').first().val() ?? '').trim();
        const $target = $preview.find(`[data-preview-slug="${slug}"]`);

        if (!$target.length) {
            return;
        }

        if (slug === 'date_of_birth') {
            if (!raw) {
                $target.text('—');
                return;
            }

            const age = ageFromDob(raw);
            $target.text(age !== null ? `${raw} (${age})` : raw);
            return;
        }

        $target.text(previewDisplayValue(raw));
    });

    const fullName = buildPreviewFullName();
    if (fieldInputValue('last_name') !== null || fieldInputValue('first_name') !== null) {
        $preview.find('.js-preview-full-name').text(fullName);
    }

    const heightRaw = fieldInputValue('height_cm');
    const weightRaw = fieldInputValue('weight_kg');
    if (heightRaw !== null || weightRaw !== null) {
        const height = Number.parseFloat(heightRaw ?? '');
        const weight = Number.parseFloat(weightRaw ?? '');
        const $bmi = $preview.find('.js-preview-bmi');

        if (Number.isFinite(height) && Number.isFinite(weight) && height > 0 && weight > 0) {
            const heightM = height / 100;
            const bmi = Math.round((weight / (heightM * heightM)) * 10) / 10;
            const category = asianBmiCategory(bmi);
            $bmi.text(`${bmi.toFixed(1)} (${category})`);
        } else if (heightRaw !== null && weightRaw !== null) {
            $bmi.text('— (—)');
        }
    }

    const systolicRaw = fieldInputValue('systolic');
    const diastolicRaw = fieldInputValue('diastolic');
    if (systolicRaw !== null || diastolicRaw !== null) {
        const $bp = $preview.find('.js-preview-bp');
        const systolic = String(systolicRaw ?? '').trim();
        const diastolic = String(diastolicRaw ?? '').trim();

        if (systolic !== '' && diastolic !== '') {
            const category = bpCategoryFromValues(systolic, diastolic);
            $bp
                .text(`${systolic}/${diastolic} (${category ?? '—'})`)
                .toggleClass('text-rose-600 font-semibold', category === 'Hypertensive Crisis')
                .toggleClass('text-slate-800', category !== 'Hypertensive Crisis');
        } else if (systolicRaw !== null && diastolicRaw !== null) {
            $bp
                .text('— (—)')
                .removeClass('text-rose-600 font-semibold')
                .addClass('text-slate-800');
        }
    }
}

$(function () {
    let searchTimer = null;

    $(document).on('input', '.js-live-search [name="q"]', function () {
        const form = $(this).closest('form').get(0);
        const input = this;

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {
            sessionStorage.setItem('clientsLiveSearchFocus', '1');
            sessionStorage.setItem(
                'clientsLiveSearchCaret',
                String(input.selectionStart ?? input.value.length),
            );
            form.requestSubmit ? form.requestSubmit() : form.submit();
        }, 300);
    });

    const $input = $('.js-live-search [name="q"]').first();

    if ($input.length && sessionStorage.getItem('clientsLiveSearchFocus') === '1') {
        const caret = Number.parseInt(
            sessionStorage.getItem('clientsLiveSearchCaret') || String($input.val().length),
            10,
        );

        $input.trigger('focus');

        const el = $input.get(0);
        if (el && typeof el.setSelectionRange === 'function') {
            const pos = Math.min(caret, String($input.val() ?? '').length);
            el.setSelectionRange(pos, pos);
        }

        sessionStorage.removeItem('clientsLiveSearchFocus');
        sessionStorage.removeItem('clientsLiveSearchCaret');
    }

    $(document).on(
        'input change',
        '[data-field-slug="height_cm"] input, [data-field-slug="height_cm"] textarea, [data-field-slug="weight_kg"] input, [data-field-slug="weight_kg"] textarea',
        updateBmiPanel,
    );

    $(document).on(
        'input change',
        '[data-field-slug="systolic"] input, [data-field-slug="systolic"] textarea, [data-field-slug="diastolic"] input, [data-field-slug="diastolic"] textarea',
        updateBloodPressurePanel,
    );

    $(document).on(
        'input change',
        '[data-field-slug] input, [data-field-slug] textarea, [data-field-slug] select',
        updateCurrentVisitPreview,
    );

    updateBmiPanel();
    updateBloodPressurePanel();
    updateCurrentVisitPreview();
});
