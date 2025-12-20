/**
 * AdminLayout - Placeholder component
 * 
 * This is a scaffold component. Full implementation to follow.
 */

import type { ReactNode } from 'react';

export interface AdminLayoutProps {
  children: ReactNode;
  title?: string;
}

export function AdminLayout({ children, title = 'Admin' }: AdminLayoutProps) {
  return (
    <div className="admin-layout">
      <header>
        <h1>{title}</h1>
      </header>
      <main>{children}</main>
    </div>
  );
}

