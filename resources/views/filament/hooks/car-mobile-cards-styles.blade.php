<style>
    @media (max-width: 767px) {
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-ctn,
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-content,
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-table,
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-table > tbody {
            display: block;
            width: 100%;
            min-width: 0 !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-table > thead {
            display: none;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-row {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0;
            margin: 0 0 0.875rem;
            padding: 0.75rem;
            border: 1px solid rgba(128, 128, 128, 0.22);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.72);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .dark .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-row {
            background: rgba(24, 24, 27, 0.85);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell {
            display: none !important;
            border: 0 !important;
            padding: 0 !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell.car-mobile-card-cell,
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell:has(.fi-ta-actions),
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell:has(input[type='checkbox']) {
            display: block !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell.car-mobile-card-cell {
            flex: 1 1 100%;
            min-width: 0;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .car-mobile-card-cell .fi-ta-col-wrp,
        .fi-resource-list-records-page:has(.car-mobile-card-cell) .car-mobile-card-cell .fi-ta-col-wrp > a {
            width: 100%;
            min-width: 0;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell:has(input[type='checkbox']) {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            z-index: 1;
            width: 1.5rem !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-cell:has(.fi-ta-actions) {
            flex: 1 1 100%;
            margin-top: 0.45rem;
            padding-top: 0.45rem !important;
            border-top: 1px solid rgba(128, 128, 128, 0.18) !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-actions-cell > div {
            padding: 0.4rem 0 0 !important;
        }

        .fi-resource-list-records-page:has(.car-mobile-card-cell) .fi-ta-actions {
            justify-content: flex-end;
            width: 100%;
        }

        .car-mobile-card {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            white-space: normal;
        }

        .car-mobile-card__hero {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            padding-right: 1.75rem;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .car-mobile-card__photo {
            width: 5.5rem;
            height: 4.25rem;
            object-fit: cover;
            border-radius: 0.7rem;
            flex-shrink: 0;
            background: rgba(128, 128, 128, 0.12);
        }

        .car-mobile-card__photo--empty {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: rgba(113, 113, 122, 1);
        }

        .car-mobile-card__icon {
            font-size: 1.25rem;
            color: rgb(245, 158, 11);
        }

        .car-mobile-card__hero-body {
            min-width: 0;
            flex: 1;
        }

        .car-mobile-card__name {
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .car-mobile-card__meta {
            margin-top: 0.15rem;
            font-size: 0.75rem;
            color: rgb(113, 113, 122);
        }

        .car-mobile-card__price {
            margin-top: 0.35rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            align-items: baseline;
        }

        .car-mobile-card__price-now {
            font-weight: 700;
            font-size: 0.9rem;
        }

        .car-mobile-card__price-old {
            font-size: 0.75rem;
            text-decoration: line-through;
            color: rgb(161, 161, 170);
        }

        .car-mobile-card__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
        }

        .car-mobile-card__badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.15rem 0.5rem;
            font-size: 0.68rem;
            font-weight: 600;
            background: rgba(128, 128, 128, 0.12);
        }

        .car-mobile-card__badge.is-success {
            background: rgba(22, 163, 74, 0.15);
            color: rgb(21, 128, 61);
        }

        .car-mobile-card__badge.is-danger {
            background: rgba(220, 38, 38, 0.15);
            color: rgb(185, 28, 28);
        }

        .car-mobile-card__badge.is-warning {
            background: rgba(217, 119, 6, 0.16);
            color: rgb(180, 83, 9);
        }

        .car-mobile-card__details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.25rem 0.65rem;
            margin: 0;
            min-width: 0;
        }

        .car-mobile-card__details > div {
            min-width: 0;
        }

        .car-mobile-card__details dt {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: rgb(113, 113, 122);
        }

        .car-mobile-card__details dd {
            margin: 0.1rem 0 0;
            font-size: 0.78rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        @media (max-width: 480px) {
            .car-mobile-card {
                gap: 0.55rem;
            }

            .car-mobile-card__hero {
                gap: 0.6rem;
            }

            .car-mobile-card__photo {
                width: 5rem;
                height: 3.85rem;
            }

            .car-mobile-card__name {
                font-size: 0.875rem;
            }
        }
    }

    @media (min-width: 768px) {
        .car-mobile-card {
            display: none;
        }
    }
</style>
