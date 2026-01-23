'use client';
import React from 'react'
import { useEffect, useState } from 'react';
import 'keen-slider/keen-slider.min.css';
import KeenSlider from 'keen-slider';
import { createRoot } from 'react-dom/client';

const Singleslider = () => {
    const [count, setCount] = useState<number>(0);
    let windowWidth: number = 0;
    if (typeof window !== "undefined") {
        windowWidth = window?.innerWidth;
    }

    useEffect(() => {
        // if (windowWidth >= 1024) {
            const slider = document.querySelector('.common-single-process-section');
            const elements = document.querySelectorAll('.keen-slider__slide');
            setCount(elements.length);
            
           
            if (slider && count > 1) {
                const container = document.createElement('div');
                const root = createRoot(container);
                root.render(<SliderArrow />);
                slider.appendChild(container);                 
            }
        // }
    }, [count,windowWidth]);

    return null; 
}

export default Singleslider;


const SliderArrow = () => {
    const [slideCount ,setSlideCount] = useState(0);

    const slider = new KeenSlider(".single-slider", {
        initial: 0,
        mode: "free-snap",
        slides: {
            perView: 1,
        },
    });

    

    const handlePrev = ()=> {
        slider.prev();
    };

    const handleNext = ()=> {
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
