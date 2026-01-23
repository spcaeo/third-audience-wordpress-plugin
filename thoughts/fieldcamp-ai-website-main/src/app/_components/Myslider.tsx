"use client";
import React, { useEffect, useState, useRef } from "react";
import "keen-slider/keen-slider.min.css";
import KeenSlider from "keen-slider";
import { createRoot } from "react-dom/client";

const SliderCode = () => {
  let windowWidth: number = 0;
  if (typeof window !== "undefined") {
    windowWidth = window?.innerWidth;
  }

  const getNumberOfItems = (width: number) => {
    if (width >= 1024) return 2; // Desktop: 3 slides
    if (width >= 768) return 2;  // Tablet: 2 slides
    return 1;                    // Mobile: 1 slide
  };

  const noOfItems = getNumberOfItems(windowWidth);

  useEffect(() => {
    const slider = document.querySelector(".common-process-section");
    const elements = slider?.querySelectorAll(".keen-slider__slide");
    const slideCount = elements?.length || 0;
    
    if (slider && slideCount > 1) {
      const keenSlider = new KeenSlider(slider as HTMLElement, {
        slides: {
          perView: noOfItems,
          spacing: 20, // Add gap between slides
        },
        created(s) {
          console.log("Slider created with", noOfItems, "slides per view");
        },
      });

      const existingControls = slider.querySelector(
        ".slider-controls-container"
      );
      if (!existingControls) {
        const container = document.createElement("div");
        container.className = "slider-controls-container";
        const root = createRoot(container);
        root.render(<SliderArrow count={slideCount} noOfItems={noOfItems} />);
        slider.appendChild(container);
      }

      // Add padding to create side gaps
      (slider as HTMLElement).style.padding = "0 20px";

      return () => {
        if (keenSlider) {
          keenSlider.destroy();
        }
      };
    }
  }, [noOfItems]);

  // Add window resize listener to update slides count
  useEffect(() => {
    const handleResize = () => {
      if (typeof window !== "undefined") {
        const width = window.innerWidth;
        const newNoOfItems = getNumberOfItems(width);
        if (newNoOfItems !== noOfItems) {
          // Reload the page to reinitialize the slider
          window.location.reload();
        }
      }
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [noOfItems]);

  return null;
};

export default SliderCode;

const SliderArrow = ({ count , noOfItems}: { count: number , noOfItems : number}) => {
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isPlaying, setIsPlaying] = useState(true);
  const sliderRef = useRef<any>(null);
  const autoplayRef = useRef<any>(null);

  useEffect(() => {
    // Initialize KeenSlider
    sliderRef.current = new KeenSlider(".my-slider", {
      initial: 0,
      mode: "free-snap",
      loop: true,
      slides: {
        perView: noOfItems || 1,
        origin: "center",
        spacing: 30,
      },
      slideChanged(slider) {
        setCurrentSlide(slider.track.details.rel);
      },
    });

    // Start autoplay
    startAutoplay();

    const sliderElement = document.querySelector(".my-slider");
    sliderElement?.addEventListener("mouseover", pauseAutoplay);
    sliderElement?.addEventListener("mouseout", startAutoplay);

    return () => {
      sliderRef.current?.destroy();
      clearInterval(autoplayRef.current);
      sliderElement?.removeEventListener("mouseover", pauseAutoplay);
      sliderElement?.removeEventListener("mouseout", startAutoplay);
    };
  }, []);

  const startAutoplay = () => {
    clearInterval(autoplayRef.current);
    autoplayRef.current = setInterval(() => {
      sliderRef.current?.next();
    }, 3000);
  };

  const pauseAutoplay = () => {
    clearInterval(autoplayRef.current);
  };

  const toggleAutoplay = () => {
    if (isPlaying) {
      pauseAutoplay();
    } else {
      startAutoplay();
    }
    setIsPlaying(!isPlaying);
  };

  return (
    <div className="slider-container w-full max-w-4xl mx-auto">
      <div className="controls-container mt-8 flex items-center justify-center gap-4">
        {/* Play/Pause Button */}
        <button
          onClick={toggleAutoplay}
          className="bg-[#EFEFF2] hover:bg-gray-200 p-2 rounded-full shadow-md transition-all"
          aria-label={isPlaying ? "Pause slider" : "Play slider"}
        >
          {isPlaying ? (
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <rect x="6" y="4" width="4" height="16" />
              <rect x="14" y="4" width="4" height="16" />
            </svg>
          ) : (
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path d="M8 5v14l11-7z" />
            </svg>
          )}
        </button>
        {/* Dots */}
        <div className="dots h-11 bg-[#EFEFF2] rounded-full px-4 flex items-center justify-center">
          {Array.from({ length: count }).map((_, idx) => (
            <button
              key={idx}
              onClick={() => {
                sliderRef.current?.moveToIdx(idx);
                setCurrentSlide(idx);
                pauseAutoplay();
              }}
              className={
                "dot w-2.5 h-2.5 rounded-full mx-2 transition-colors " +
                (currentSlide === idx ? "bg-[#717174] active" : "bg-[#717174]")
              }
            />
          ))}
        </div>
      </div>
    </div>
  );
};
