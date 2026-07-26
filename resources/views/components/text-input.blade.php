@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-teal-600 focus:ring-teal-600 rounded-md shadow-sm text-base sm:text-sm']) }}>
