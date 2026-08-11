@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-lg border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-green-700 focus:ring-green-100 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500']) }}>
