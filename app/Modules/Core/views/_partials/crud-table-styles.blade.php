{{-- أنماط موحدة لجداول CRUD (Tenants, Roles, Permissions، وأي قائمة لاحقة) --}}
<style>
  .crud-table .table { direction: inherit; }
  .crud-table .table-actions {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
  }
  .crud-table .table-actions .btn-icon,
  .crud-table .table-actions form { flex-shrink: 0; }
  .crud-table .table-actions .btn-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }
  .crud-table .table-actions .btn-icon .icon-base { font-size: 1.25rem; }
  .crud-table .table-actions .btn-text-primary,
  .crud-table .table-actions .btn-text-primary .icon-base { color: var(--bs-primary) !important; }
  .crud-table .table-actions .btn-text-primary:hover { background: rgba(var(--bs-primary-rgb), 0.08); color: var(--bs-primary) !important; }
  .crud-table .table-actions .btn-text-primary:hover .icon-base { color: var(--bs-primary) !important; }
  .crud-table .table-actions .btn-text-warning,
  .crud-table .table-actions .btn-text-warning .icon-base { color: var(--bs-warning) !important; }
  .crud-table .table-actions .btn-text-warning:hover { background: rgba(var(--bs-warning-rgb), 0.08); color: var(--bs-warning) !important; }
  .crud-table .table-actions .btn-text-warning:hover .icon-base { color: var(--bs-warning) !important; }
  .crud-table .table-actions .btn-text-danger,
  .crud-table .table-actions .btn-text-danger .icon-base { color: var(--bs-danger) !important; }
  .crud-table .table-actions .btn-text-danger:hover { background: rgba(var(--bs-danger-rgb), 0.08); color: var(--bs-danger) !important; }
  .crud-table .table-actions .btn-text-danger:hover .icon-base { color: var(--bs-danger) !important; }
</style>
