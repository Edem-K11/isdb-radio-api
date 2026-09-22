{{-- Shown above the breadcrumbs, only on a resource's Create/Edit pages (see
     AdminPanelProvider — the PAGE_START render hook decides when this
     partial even gets rendered). Goes straight up the hierarchy to that
     resource's list, not "wherever the browser was before" — an edit page
     reached by any route still belongs to one specific list.

     Plain CSS here, not Tailwind utility classes: Filament's admin panel
     ships its own precompiled stylesheet, built once from Filament's own
     package views — it never scans this app's custom Blade files, so any
     Tailwind class used here silently has no matching rule at all. That's
     exactly why the icon rendered at its raw, unstyled (huge) size before. --}}
<div style="padding: 1.25rem 1.5rem 0;">
    <a
        href="{{ $url }}"
        wire:navigate
        aria-label="Retour"
        title="Retour"
        class="isdb-back-button"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1.25rem; height: 1.25rem; display: block;">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.66l4.1 3.95a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08l-4.1 3.95h10.59A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>
    </a>
</div>
<style>
    .isdb-back-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        color: #6b7280;
        text-decoration: none;
        transition: color 0.15s ease, background-color 0.15s ease;
    }
    .isdb-back-button:hover {
        color: #374151;
        background-color: rgba(0, 0, 0, 0.04);
    }
    :is(.dark) .isdb-back-button {
        color: #9ca3af;
    }
    :is(.dark) .isdb-back-button:hover {
        color: #e5e7eb;
        background-color: rgba(255, 255, 255, 0.06);
    }
</style>
