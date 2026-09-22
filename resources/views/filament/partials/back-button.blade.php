{{-- Shown above the breadcrumbs on every panel page (registered as a
     panel-wide PAGE_START render hook — see AdminPanelProvider). Plain
     browser history back, so it works the same way regardless of which
     resource/page it's on: Edit/Create → back to the list, list → back to
     wherever the admin came from. --}}
<div class="fi-back-button-ctn px-4 sm:px-6 lg:px-8 pt-6">
    <button
        type="button"
        onclick="window.history.back()"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 flex-shrink-0">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.66l4.1 3.95a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08l-4.1 3.95h10.59A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>
        Retour
    </button>
</div>
