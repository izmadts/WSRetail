{{--
    Small "?" icon that reveals a detailed explanation on mouse hover only -
    for a setting/field whose effect isn't obvious from its label alone.
    Pure CSS (Tailwind's group/group-hover), no click handling and no
    Alpine state at all - it can never intercept a click meant for
    something else nearby (e.g. a checkbox it sits next to), and there's
    nothing to accidentally leave open. Always opens below-left of the icon
    so it never gets clipped at the top of the page.

    Usage: <x-help-tooltip>Explanation text, can be a full sentence or two.</x-help-tooltip>
--}}
<span class="relative inline-flex align-middle ml-1 group">
    <button type="button"
        class="inline-flex items-center justify-center w-4 h-4 rounded-full border border-gray-300 bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600 group-hover:border-blue-300 text-[10px] font-bold leading-none cursor-help transition-colors"
        aria-label="Help">?</button>
    <div class="hidden group-hover:block absolute z-30 top-full mt-2 left-0 w-72 max-w-[80vw] rounded-lg bg-gray-900 text-gray-100 text-xs leading-relaxed p-3 shadow-xl">
        {{ $slot }}
    </div>
</span>
