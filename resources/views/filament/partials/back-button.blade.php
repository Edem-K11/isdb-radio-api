{{-- Shown above the breadcrumbs, only on a resource's Create/Edit pages (see
     AdminPanelProvider — the PAGE_START render hook decides when this
     partial even gets rendered). Goes straight up the hierarchy to that
     resource's list, not "wherever the browser was before" — an edit page
     reached by any route still belongs to one specific list. --}}
<div class="fi-back-button-ctn px-4 sm:px-6 lg:px-8 pt-6">
    <a
        href="{{ $url }}"
        wire:navigate
        aria-label="Retour"
        title="Retour"
        class="inline-flex items-center justify-center rounded-lg p-1 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 flex-shrink-0">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.66l4.1 3.95a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08l-4.1 3.95h10.59A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>
    </a>
</div>
