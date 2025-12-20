/**
 * Menu and navigation types
 */

export type MenuItemType = 'link' | 'group' | 'divider';

export type MenuItem = {
  id: string;
  type: MenuItemType;
  label: string;
  icon?: string;
  url?: string;
  route?: string;
  badge?: string | number;
  children?: MenuItem[];
  permission?: string;
  order: number;
  visible?: boolean;
};

export type MenuGroup = {
  id: string;
  label: string;
  icon?: string;
  items: MenuItem[];
  order: number;
  permission?: string;
};

