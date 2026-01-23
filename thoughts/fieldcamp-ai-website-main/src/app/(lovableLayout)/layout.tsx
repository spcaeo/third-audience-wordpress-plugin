import "./lovable.scss";
import HeaderContainer from "@/app/_components/Header/HeaderContainer";
import HeaderLogo from "@/app/_components/Header/HeaderLogo";
import HeaderContainerNavigation from "@/app/_components/Header/HeaderContainerNavigation";
import FooterV2Dynamic from "@/app/_components/FooterV2Dynamic";


export default function LovableLayout({
  children,
}: {
  children: React.ReactNode
}) {

  return (
    <>
    <HeaderContainer>
      <HeaderLogo></HeaderLogo>
      <HeaderContainerNavigation></HeaderContainerNavigation>
    </HeaderContainer>
      <section>
          {children}
      </section>
    <FooterV2Dynamic />
    </>
  )
}