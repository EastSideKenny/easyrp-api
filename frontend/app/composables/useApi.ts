/**
 * Composable for making authenticated, tenant-scoped API calls
 * to the Laravel backend.
 *
 * Wraps useAuth().authFetch and automatically injects an
 * `X-Tenant` header with the current tenant subdomain so the
 * backend can scope requests to the correct tenant.
 *
 * Usage:
 *   const api = useApi()
 *   const users = await api('/users')
 *   await api('/posts', { method: 'POST', body: { title: 'Hello' } })
 */
export function useApi() {
  const { authFetch } = useAuth()
  const { tenantSlug } = useTenant()

  /**
   * Wrapper around authFetch that injects tenant context.
   */
  function api<T = unknown>(url: string, opts: Record<string, any> = {}): Promise<T> {
    const headers: Record<string, string> = {
      ...(opts.headers ?? {}),
    }

    if (tenantSlug.value) {
      headers['X-Tenant'] = tenantSlug.value
    }

    return authFetch<T>(url, { ...opts, headers })
  }

  return api
}
