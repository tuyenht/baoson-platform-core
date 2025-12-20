/**
 * Licensing and subscription types
 */

export type LicenseTier = 'free' | 'starter' | 'professional' | 'enterprise';

export type LicenseFeature = {
  id: string;
  name: string;
  enabled: boolean;
  limit?: number;
  usage?: number;
};

export type License = {
  id: string;
  tenantId: string;
  tier: LicenseTier;
  features: LicenseFeature[];
  startsAt: Date;
  expiresAt?: Date;
  isActive: boolean;
  createdAt: Date;
  updatedAt: Date;
};

export type Subscription = {
  id: string;
  licenseId: string;
  planId: string;
  status: 'active' | 'canceled' | 'past_due' | 'trialing';
  currentPeriodStart: Date;
  currentPeriodEnd: Date;
  cancelAtPeriodEnd: boolean;
};

