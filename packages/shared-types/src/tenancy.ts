/**
 * Multi-tenancy types
 */

export type TenantId = string;
export type SiteId = string;

export type Tenant = {
  id: TenantId;
  name: string;
  slug: string;
  domain?: string;
  isActive: boolean;
  settings: Record<string, unknown>;
  createdAt: Date;
  updatedAt: Date;
};

export type Site = {
  id: SiteId;
  tenantId: TenantId;
  name: string;
  slug: string;
  domain?: string;
  isActive: boolean;
  settings: Record<string, unknown>;
  createdAt: Date;
  updatedAt: Date;
};

export type TenantContext = {
  tenantId: TenantId;
  siteId?: SiteId;
  tenant?: Tenant;
  site?: Site;
};

