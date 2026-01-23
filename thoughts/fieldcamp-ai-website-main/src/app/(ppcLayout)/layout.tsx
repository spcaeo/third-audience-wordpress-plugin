import "./lovable.scss";
import "../(lovableLayout)/lovable.scss";
import HeaderContainer from "@/app/_components/Header/HeaderContainer";
import HeaderLogo from "@/app/_components/Header/HeaderLogo";
import FooterV2Dynamic from "@/app/_components/FooterV2Dynamic";
import PPCHeaderContainerNavigation from "@/app/_components/Header/ppcHeaderContainerNavigation";

export default function LovableLayout({
  children,
}: {
  children: React.ReactNode
}) {

  return (
      <>
      <HeaderContainer>
        <HeaderLogo withLink={false}></HeaderLogo>
        <PPCHeaderContainerNavigation></PPCHeaderContainerNavigation>
      </HeaderContainer>
        <section>
            {children}
        </section>
      <FooterV2Dynamic />
      </>
    )
}