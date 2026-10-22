{{--
    Vista de paginación por defecto de todo el proyecto.

    Sustituye la paginación "tailwind" de Laravel, que trae flechas SVG sin
    estilos y etiquetas en inglés no traducidas (pagination.previous/next).

    Estilo: tarjetas de vidrio con las variables CSS del tema (dark / light).
--}}
@if ($paginator->hasPages())
<nav class="pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2" role="navigation" aria-label="Navegación de páginas">

    <p class="text-muted small mb-0">
        Mostrando
        <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
        a
        <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
        de
        <span class="fw-semibold">{{ $paginator->total() }}</span>
        registros
    </p>

    <ul class="pagination pagination-glass mb-0">
        {{-- Página anterior --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior">
                    <i class="fa-solid fa-chevron-left"></i> Anterior
                </a>
            </li>
        @endif

        {{-- Números de página (con "..." para los huecos) --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link page-dots">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active" aria-current="page">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}" aria-label="Ir a la página {{ $page }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Página siguiente --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link" aria-hidden="true">
                    Siguiente <i class="fa-solid fa-chevron-right"></i>
                </span>
            </li>
        @endif
    </ul>
</nav>

<style>
    .pagination-glass {
        --bs-pagination-bg: transparent;
        --bs-pagination-border-color: transparent;
        gap: 5px;
    }

    .pagination-glass .page-item .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 34px;
        height: 34px;
        padding: 0 11px;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1;
        color: var(--text-primary);
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 9px;
        text-decoration: none;
        transition: background-color var(--transition, 0.2s ease),
                    border-color var(--transition, 0.2s ease),
                    color var(--transition, 0.2s ease);
    }

    .pagination-glass .page-item:not(.disabled) .page-link:hover {
        background: var(--bg-card-hover, var(--bg-card));
        border-color: var(--border-hover, var(--border));
        color: var(--amber, #e6a86c);
    }

    .pagination-glass .page-item.active .page-link {
        background: var(--accent);
        border-color: var(--accent);
        color: #1a0e04;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    }

    .pagination-glass .page-item.disabled .page-link {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .pagination-glass .page-link.page-dots {
        background: transparent;
        border-color: transparent;
        min-width: 26px;
        padding: 0 2px;
    }

    [data-bs-theme="light"] .pagination-glass .page-item .page-link {
        background: var(--bg-card);
        border-color: var(--border);
        color: var(--text-primary);
    }
</style>
@endif
