'use client';
import React from 'react';
import { useEffect, useState } from 'react';
import 'keen-slider/keen-slider.min.css';
import KeenSlider from 'keen-slider';
import { createRoot } from 'react-dom/client';
import './Thumbnailslider.scss';

const Thumbnailslider = () => {
    const [count, setCount] = useState<number>(0);
    let windowWidth: number = 0;
    if (typeof window !== "undefined") {
        windowWidth = window?.innerWidth;
    }

    useEffect(() => {
        const sliderSection = document.querySelector('.thumbnail-slider-section');
        const mainSlides = document.querySelectorAll('.main-content-slider .keen-slider__slide');
        const thumbnailSlides = document.querySelectorAll('.thumbnail-navigation .thumbnail-slide');
        setCount(mainSlides.length);

        if (sliderSection && count > 1) {
            const container = document.createElement('div');
            const root = createRoot(container);
            root.render(<SliderController />);
            sliderSection.appendChild(container);
        }
    }, [count, windowWidth]);

    return null;
}

export default Thumbnailslider;

const SliderController = () => {
    const [currentSlide, setCurrentSlide] = React.useState(0);
    const [mainSlider, setMainSlider] = React.useState<any>(null);

    React.useEffect(() => {
        // Initialize main content slider
        const slider = new KeenSlider(".main-content-slider", {
            initial: 0,
            loop: false,
            slides: {
                perView: 1,
                spacing: 0,
            },
            slideChanged(s) {
                const currentIdx = s.track.details.rel;
                setCurrentSlide(currentIdx);
                updateActiveThumbnail(currentIdx);
            },
        });

        setMainSlider(slider);

        // Add click handlers to thumbnail slides
        const thumbnails = document.querySelectorAll('.thumbnail-navigation .thumbnail-slide');
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.addEventListener('click', () => {
                slider.moveToIdx(index);
            });

            // Add cursor pointer and set initial active state
            (thumbnail as HTMLElement).style.cursor = 'pointer';
            if (index === 0) {
                thumbnail.classList.add('active');
            }
        });

        // Set initial active state
        updateActiveThumbnail(0);

        return () => {
            slider.destroy();
        };
    }, []);

    const updateActiveThumbnail = (index: number) => {
        const thumbnails = document.querySelectorAll('.thumbnail-navigation .thumbnail-slide');
        thumbnails.forEach((thumbnail, idx) => {
            if (idx === index) {
                thumbnail.classList.add('active');
            } else {
                thumbnail.classList.remove('active');
            }
        });
    };

    const handlePrev = () => {
        if (mainSlider) {
            mainSlider.prev();
        }
    };

    const handleNext = () => {
        if (mainSlider) {
            mainSlider.next();
        }
    };

    const isAtStart = currentSlide === 0;
    const isAtEnd = mainSlider && currentSlide === mainSlider.track.details.slides.length - 1;

    return <>
        <div className="nav-arrow">
            <Arrow
                left
                onClick={handlePrev}
                disabled={isAtStart}
            />
            <Arrow
                onClick={handleNext}
                disabled={isAtEnd}
            />
        </div>
    </>
}

function Arrow(props: {
    disabled: boolean
    left?: boolean
    onClick: () => void
}) {
    const disabeld = props.disabled ? " arrow--disabled" : ""
    return (
        <svg
            onClick={props.onClick}
            className={`arrow ${props.left ? "arrow--left" : "arrow--right"
                } ${disabeld}`}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
        >
            {props.left && (
                <path d="M16.67 0l2.83 2.829-9.339 9.175 9.339 9.167-2.83 2.829-12.17-11.996z" />
            )}
            {!props.left && (
                <path d="M5 3l3.057-3 11.943 12-11.943 12-3.057-3 9-9z" />
            )}
        </svg>
    )
}
