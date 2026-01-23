import React from "react";
import "./header.scss"


export default function HeaderContainer({ children,layout = 'default' }: { children: any,layout?:string }) {
    return <header className={`bg-white/70 backdrop-blur-sm w-full max-w-full bg-white fixed left-0 right-0 bottom-auto z-20 px-5 xl:px-0  top-0 transition-all duration-500`}>
    <div className=" w-full  max-w-full xl:max-w-[1245px] 2xl:max-w-[1245px]   mx-auto navbar_wrapper relative flex items-center justify-center z-40">
        <div className={`navbar_container w-full h-full min-h-[50px] lg:min-h-[72px] gap-x-6 gap-y-6 rounded-full flex items-center justify-between  ${layout == 'full' && '!max-w-full'}`}>
            {children} 
        </div>
    </div>
</header >
}