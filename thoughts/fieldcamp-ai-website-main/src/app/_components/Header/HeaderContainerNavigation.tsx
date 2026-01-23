import React from "react";
import { getMenu } from "@/lib/api";
import HeaderNavigation from "./HeaderNavigation";

export default async function HeaderContainerNavigation() {
  const menuItems = await getMenu("PRIMARY");
  const CUSTOMER_LOGIN = await getMenu("CUSTOMER_LOGIN");
  return (
    <HeaderNavigation
      menuItems={menuItems}
      CustomerLoginMenuItems={CUSTOMER_LOGIN}
    />
  );
}