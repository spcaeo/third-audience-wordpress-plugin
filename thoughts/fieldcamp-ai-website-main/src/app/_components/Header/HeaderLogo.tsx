import Image from "next/image";
import Link from "next/link";
import React from "react";
// import { getgeneralSettings } from "@/lib/api";
import Logo from "../../../../public/images/logo.svg";



export default async function HeaderLogo({withLink = true}: {withLink?: boolean}) {
    return (
        withLink ? <Link href="/" className="navbar_logo-link w-nav-brand w--current" title="Top Custom Software Development Company in Canada">
            {/* {gsettings?.headerLogo ? <Image src={gsettings?.headerLogo} alt="Header Logo" className="h-auto w-[150px] min-[1199px]:w-[180px] xl:w-[212px]" width={212} height={42} /> : ''} */}
            <Image
            src={Logo}
            alt="FieldCamp logo"
            width={130}
            height={26}
            className="logo"
          />
        </Link> : <Link href="/" className="navbar_logo-link w-nav-brand w--current" title="Top Custom Software Development Company in Canada"><Image
        src={Logo}
        alt="FieldCamp logo"
        width={130}
        height={26}
        className="logo"
      /></Link>  
    )
}