<style>
    :root {
        --sidebar-width: 260px;
        --navbar-height: 70px;
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --primary-color: #667eea;
        --secondary-color: #764ba2;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f7fa;
        overflow-x: hidden;
    }

    /* Top Navbar */
    .top-navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: var(--navbar-height);
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        z-index: 1000;
        display: flex;
        align-items: center;
        padding: 0 2rem;
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-brand i {
        font-size: 1.8rem;
        -webkit-text-fill-color: var(--primary-color);
    }

    .navbar-right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .notification-btn {
        position: relative;
        background: transparent;
        border: none;
        font-size: 1.3rem;
        color: #64748b;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .notification-btn:hover {
        background: #f1f5f9;
        color: var(--primary-color);
    }

    .notification-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #ef4444;
        color: white;
        font-size: 0.65rem;
        padding: 0.15rem 0.4rem;
        border-radius: 10px;
        font-weight: 600;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .user-profile:hover {
        background: #f1f5f9;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #1e293b;
    }

    .user-role {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        left: 0;
        top: var(--navbar-height);
        bottom: 0;
        width: var(--sidebar-width);
        background: white;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
        z-index: 999;
        overflow-y: auto;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 1rem 1.5rem 1px;
        border-bottom: 1px solid #e2e8f0;
    }

    .sidebar-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .sidebar-menu {
        list-style: none;
        padding: 1rem 0 0.25rem 0;
    }

    .menu-item {
        margin-bottom: 0.25rem;
    }

    .menu-link {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1.5rem;
        color: #64748b;
        text-decoration: none;
        transition: all 0.3s;
        position: relative;
        font-weight: 500;
    }

    .menu-link:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        color: var(--primary-color);
    }

    .menu-link.active {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, transparent 100%);
        color: var(--primary-color);
        font-weight: 600;
    }

    .menu-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary-gradient);
        border-radius: 0 4px 4px 0;
    }

    .menu-link i {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
    }

    .menu-badge {
        margin-left: auto;
        background: #fee2e2;
        color: #dc2626;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-weight: 600;
    }

    .sidebar-footer {
        position: sticky;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1.5rem;
        margin-top: auto;
        border-top: 1px solid #e2e8f0;
        background: white;
        z-index: 10;
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        width: 100%;
        padding: 0.875rem 1.5rem;
        background: transparent;
        border: 1px solid #e2e8f0;
        color: #64748b;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 500;
    }

    .logout-btn:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    .logout-btn i {
        font-size: 1.2rem;
    }

    /* Main Content */
    .main-content {
        margin-left: var(--sidebar-width);
        margin-top: var(--navbar-height);
        padding: 2rem;
        min-height: calc(100vh - var(--navbar-height));
        transition: all 0.3s ease;
    }

    /* Mobile Toggle */
    .mobile-toggle {
        display: none;
        background: transparent;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0.5rem;
    }

    /* Task Table Styles */
    .task-table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .task-table {
        margin-bottom: 0;
    }

    .task-table thead {
        background: var(--primary-gradient);
        color: white;
    }

    .task-table thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
        white-space: nowrap;
        font-size: 0.9rem;
    }

    .task-table tbody tr {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .task-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .task-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }

    .task-id {
        font-weight: 600;
        color: #667eea;
        font-size: 0.85rem;
    }

    .task-title-cell {
        /* padding: 1rem; */
        max-width: 250px;
    }

    .task-title-wrapper {
        margin-bottom: 0.5rem;
    }

    .task-meta {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .task-title-text {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .task-description-preview {
        font-size: 0.85rem;
        color: #718096;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-badge.pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-badge.in-progress {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .status-badge.completed {
        background-color: #d1fae5;
        color: #065f46;
    }

    .status-badge.overdue {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-badge.overdue-critical {
        background-color: #fecaca;
        color: #7f1d1d;
        font-weight: 600;
        animation: pulse-subtle 2s infinite;
    }

    @keyframes pulse-subtle {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: 0.9;
            transform: scale(1.02);
        }
    }

    /* .task-description-preview {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        } */

    /* Responsive */
    @media (max-width: 768px) {
        /* .task-title-text {
                font-size: 0.9rem;
            } */

        /* .status-badge {
                font-size: 0.75rem;
                padding: 0.25rem 0.6rem;
            }

            .task-description-preview {
                font-size: 0.8rem;
            } */
    }

    .category-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: #e0e7ff;
        color: #4338ca;
        display: inline-block;
    }

    .priority-stars {
        color: #fbbf24;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .date-text {
        font-size: 0.85rem;
        color: #64748b;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .pagination-container {
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        margin-top: 1rem;
    }

    .table-info {
        font-size: 0.9rem;
        color: #64748b;
    }

    /* Responsive */
    /* //!มือถือ */
    @media (max-width: 576px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
            /* overflow-x: hidden; */
        }

        .main-content .container-fluid {
            padding-left: 0;
            padding-right: 0;
        }
    }

    @media (max-width: 992px) {
        .sidebar {
            left: calc(var(--sidebar-width) * -1);
        }

        .sidebar.show {
            left: 0;
        }

        .main-content {
            margin-left: 0;
        }

        .mobile-toggle {
            display: block;
        }

        .user-info {
            display: none;
        }

        .top-navbar {
            padding: 0 0.5rem;
        }

        .navbar-right {
            gap: 0.5rem;
        }

        .user-profile {
            padding: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        .task-table-container {
            overflow-x: auto;
        }

        .task-table {
            min-width: 800px;
        }

        .task-title-cell {
            max-width: 200px;
        }
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: var(--navbar-height);
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
    }

    .sidebar-overlay.show {
        display: block;
    }
</style>