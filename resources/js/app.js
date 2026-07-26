import './bootstrap';
import $ from 'jquery';
import Alpine from 'alpinejs';
import { autosaveField } from './autosave';

window.$ = window.jQuery = $;
window.Alpine = Alpine;
window.autosaveField = autosaveField;

Alpine.start();

function asianBmiCategory(bmi) {
    if (bmi < 18.5) return 'Underweight';
    if (bmi < 23) return 'Normal';
    if (bmi < 25) return 'Overweight';
    if (bmi < 30) return 'Obese I';
    return 'Obese II';
}

function updateBmiPanel() {
    const $panel = $('.js-bmi-panel');
    if (!$panel.length) return;

    const height = Number.parseFloat($('[data-field-slug="height_cm"] input, [data-field-slug="height_cm"] textarea, [data-field-slug="height_cm"] select').val());
    const weight = Number.parseFloat($('[data-field-slug="weight_kg"] input, [data-field-slug="weight_kg"] textarea, [data-field-slug="weight_kg"] select').val());

    if (!Number.isFinite(height) || !Number.isFinite(weight) || height <= 0 || weight <= 0) {
        $panel.find('.js-bmi-value, .js-bmi-category').text('—');
        return;
    }

    const heightM = height / 100;
    const bmi = Math.round((weight / (heightM * heightM)) * 10) / 10;

    $panel.find('.js-bmi-value').text(bmi.toFixed(1));
    $panel.find('.js-bmi-category').text(asianBmiCategory(bmi));
}

function bpCategoryFromValues(systolic, diastolic) {
    const sys = Number.parseFloat(systolic);
    const dia = Number.parseFloat(diastolic);

    if (!Number.isFinite(sys) || !Number.isFinite(dia)) {
        return null;
    }

    // AHA Blood Pressure Categories
    if (sys > 180 || dia > 120) return 'Hypertensive Crisis';
    if (sys >= 140 || dia >= 90) return 'Hypertension Stage 2';
    if ((sys >= 130 && sys <= 139) || (dia >= 80 && dia <= 89)) return 'Hypertension Stage 1';
    if (sys >= 120 && sys <= 129 && dia < 80) return 'Elevated';
    if (sys < 120 && dia < 80) return 'Normal';

    return null;
}

function updateBloodPressurePanel() {
    const $panel = $('.js-bp-panel');
    if (!$panel.length) return;

    const systolic = String($('[data-field-slug="systolic"] input, [data-field-slug="systolic"] textarea').val() ?? '').trim();
    const diastolic = String($('[data-field-slug="diastolic"] input, [data-field-slug="diastolic"] textarea').val() ?? '').trim();
    const $category = $panel.find('.js-bp-category');

    if (systolic === '' || diastolic === '') {
        $panel.find('.js-bp-value').text('—');
        $category.text('—').removeClass('text-rose-600').addClass('text-slate-800');
        return;
    }

    const category = bpCategoryFromValues(systolic, diastolic);

    $panel.find('.js-bp-value').text(`${systolic}/${diastolic}`);
    $category
        .text(category ?? '—')
        .toggleClass('text-rose-600', category === 'Hypertensive Crisis')
        .toggleClass('text-slate-800', category !== 'Hypertensive Crisis');
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

    updateBmiPanel();
    updateBloodPressurePanel();
});
