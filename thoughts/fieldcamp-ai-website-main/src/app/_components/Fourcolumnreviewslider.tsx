'use client';
import React from 'react';
import { useEffect, useState } from 'react';
import 'keen-slider/keen-slider.min.css';
import KeenSlider from 'keen-slider';
import { createRoot } from 'react-dom/client';

const Fourcolumnreviewslider = () => {
    const [count, setCount] = useState<number>(0);
    let windowWidth: number = 0;
    if (typeof window !== "undefined") {
        windowWidth = window?.innerWidth;
    }

    useEffect(() => {
        const slider = document.querySelector('.fourcolumn-review-slider-section');
        const elements = document.querySelectorAll('.fourcolumn-slider .keen-slider__slide');
        setCount(elements.length);

        if (slider && count > 1) {
            const container = document.createElement('div');
            const root = createRoot(container);
            root.render(<SliderArrow />);
            slider.appendChild(container);
        }
    }, [count, windowWidth]);

    return null;
}

export default Fourcolumnreviewslider;

const SliderArrow = () => {
    const slider = new KeenSlider(".fourcolumn-slider", {
        initial: 0,
        loop: false,
        mode: "free-snap",
        slides: {
            perView: 1,
            spacing: 20,
        },
        breakpoints: {
            "(min-width: 640px)": {
                slides: {
                    perView: 2,
                    spacing: 20,
                },
            },
            "(min-width: 768px)": {
                slides: {
                    perView: 2,
                    spacing: 20,
                },
            },
            "(min-width: 1024px)": {
                slides: {
                    perView: 3,
                    spacing: 20,
                },
            },
        },
    });

    const handlePrev = () => {
        slider.prev();
    };

    const handleNext = () => {
        slider.next();
    };

    return <>
        <div className="nav-arrow">
            <Arrow
                left
                onClick={handlePrev}
                disabled={false}
            />
            <Arrow
                onClick={handleNext}
                disabled={false}
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