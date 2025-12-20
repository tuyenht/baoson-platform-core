/**
 * Role-Based Access Control (RBAC) types
 */

export type Permission = string;

export type Role = {
  id: string;
  name: string;
  slug: string;
  permissions: Permission[];
  description?: string;
  createdAt: Date;
  updatedAt: Date;
};

export type UserRole = {
  userId: string;
  roleId: string;
  assignedAt: Date;
  assignedBy?: string;
};

export type RoleAssignment = {
  role: Role;
  assignedAt: Date;
  assignedBy?: string;
};

