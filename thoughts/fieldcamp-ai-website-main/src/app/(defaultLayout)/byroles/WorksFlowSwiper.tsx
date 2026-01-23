'use client';
import React, { useEffect, useRef, useState, useCallback } from 'react';
import './WorksFlowSwiper.scss';

interface FeatureItem {
    title: string;
    description: string;
}

interface RoleItem {
    name: string;
    features: FeatureItem[];
}

interface WorksFlowSwiperProps {
    title?: string;
    subtitle?: string;
    buttonText?: string;
    buttonLink?: string;
    roles?: RoleItem[];
}

const WorksFlowSwiper: React.FC<WorksFlowSwiperProps> = ({
    title = "Work Flows Better When It’s Built Around People",
    subtitle = "",
    roles = [
        {
            name: "Operations Director",
            features: [
                {
                    title: "Guide Teams Without Heavy Training",
                    description: "Automation keeps your team aligned without endless check-ins or manuals."
                },
                {
                    title: "Oversee Progress With Ease",
                    description: "One dashboard shows where work stands, what’s done, and what needs action."
                },
                {
                    title: "Scale Operations Without More Meetings",
                    description: "As work grows, FieldCamp automates updates so you lead without micromanaging."
                }
            ]
        },
        {
            name: "Finance Operations",
            features: [
                {
                    title: "Collect Payments Without Follow-Ups",
                    description: "Invoices are sent, reminders go out, and payments clear while you stay focused."
                },
                {
                    title: "Know Your Cash Position",
                    description: "See income, costs, and balances update automatically across every job"
                },
                {
                    title: "See Profit Margins In Real Time",
                    description: "Track job costs and earnings instantly without the mess and confusion of spreadsheets."
                }
            ]
        },
        {
            name: "Sales Operations",
            features: [
                {
                    title: "Every Deal Moves, Nothing Stalls",
                    description: "Leads turn into estimates and jobs on their own, keeping momentum strong."
                },
                {
                    title: "Follow Up Without Manually Tracking",
                    description: "Conversations, quotes, and client updates sync automatically across your team."
                },
                {
                    title: "Sales Without The Slowdown",
                    description: "From quote to job, every step moves forward without you pushing it."
                }
            ]
        },
        {
            name: "Dispatcher",
            features: [
                {
                    title: "Auto-Organized Dispatch Board",
                    description: "Jobs, routes, and techs stay perfectly aligned without your constant input."
                },
                {
                    title: "Plan Once, System Adjusts",
                    description: "When plans shift, routes and schedules update automatically to stay on track."
                },
                {
                    title: "Routes Update When Plans Change",
                    description: "AI reschedules jobs in seconds, keeping every technician on time and on route."
                }
            ]
        },
        {
            name: "Field Service Technician",
            features: [
                {
                    title: "Clear Instructions, Fast Completion",
                    description: "Every job comes with the details you need without any confusion, and only clarity."
                },
                {
                    title: "Everything You Need In One Place",
                    description: "View tasks, clients, and notes from one screen, synced live with dispatch."
                },
                {
                    title: "Know Exactly What To Do Next",
                    description: "When one job ends, the next appears instantly with all details ready."
                }
            ]
        }
    ]
}) => {
    const [activeIndex, setActiveIndex] = useState(2);
    const [scrollOffset, setScrollOffset] = useState(0);
    const timelineRef = useRef<HTMLDivElement>(null);
    const rolesContainerRef = useRef<HTMLDivElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [startY, setStartY] = useState(0);
    const [isHovering, setIsHovering] = useState(false);
    const autoPlayRef = useRef<NodeJS.Timeout | null>(null);

    // Auto-play functionality
    useEffect(() => {
        if (!isDragging && !isHovering) {
            autoPlayRef.current = setInterval(() => {
                setScrollOffset(prev => prev + 1);
            }, 3000);
        }

        return () => {
            if (autoPlayRef.current) {
                clearInterval(autoPlayRef.current);
            }
        };
    }, [isDragging, isHovering]);

    // Calculate active index based on scroll offset
    useEffect(() => {
        const normalizedOffset = ((scrollOffset % roles.length) + roles.length) % roles.length;
        setActiveIndex(Math.round(normalizedOffset) % roles.length);
    }, [scrollOffset, roles.length]);

    // Mouse/Touch handlers for drag functionality
    const handleMouseDown = useCallback((e: React.MouseEvent) => {
        setIsDragging(true);
        setStartY(e.clientY);
        if (autoPlayRef.current) {
            clearInterval(autoPlayRef.current);
        }
    }, []);

    const handleMouseMove = useCallback((e: React.MouseEvent) => {
        if (!isDragging) return;

        const deltaY = e.clientY - startY;
        const sensitivity = 0.01;
        setScrollOffset(prev => prev - deltaY * sensitivity);
        setStartY(e.clientY);
    }, [isDragging, startY]);

    const handleMouseUp = useCallback(() => {
        setIsDragging(false);
    }, []);

    const handleMouseLeave = useCallback(() => {
        setIsDragging(false);
        setIsHovering(false);
    }, []);

    const handleMouseEnter = useCallback(() => {
        setIsHovering(true);
    }, []);

    // Touch handlers for mobile
    const handleTouchStart = useCallback((e: React.TouchEvent) => {
        setIsDragging(true);
        setStartY(e.touches[0].clientY);
        if (autoPlayRef.current) {
            clearInterval(autoPlayRef.current);
        }
    }, []);

    const handleTouchMove = useCallback((e: React.TouchEvent) => {
        if (!isDragging) return;

        const deltaY = e.touches[0].clientY - startY;
        const sensitivity = 0.015;
        setScrollOffset(prev => prev - deltaY * sensitivity);
        setStartY(e.touches[0].clientY);
    }, [isDragging, startY]);

    const handleTouchEnd = useCallback(() => {
        setIsDragging(false);
    }, []);

    // Wheel handler for scroll
    const handleWheel = useCallback((e: React.WheelEvent) => {
        e.preventDefault();
        const sensitivity = 0.005;
        setScrollOffset(prev => prev + e.deltaY * sensitivity);
    }, []);

    const handleRoleClick = (index: number) => {
        const diff = index - activeIndex;
        setScrollOffset(prev => prev + diff);
    };

    const getRolePosition = (index: number) => {
        const totalRoles = roles.length;
        const normalizedOffset = scrollOffset % totalRoles;

        // Calculate position relative to scroll
        let relativePos = index - normalizedOffset;

        // Wrap around for infinite scroll effect
        while (relativePos < -totalRoles / 2) relativePos += totalRoles;
        while (relativePos > totalRoles / 2) relativePos -= totalRoles;

        // Map position to visual properties
        const centerPos = 2; // Center position index
        const distanceFromCenter = relativePos - (centerPos - 2);

        // Calculate properties based on distance from center
        const maxDistance = 2.5;
        const clampedDistance = Math.max(-maxDistance, Math.min(maxDistance, distanceFromCenter));

        // Curved arc positioning - items follow an arc path
        const topPercent = 50 + (clampedDistance * 11);

        // Rotation: left to right tilt following the arc curve
        const rotation = clampedDistance * 6; // top items: left-low right-high, bottom items: left-high right-low

        // Show only 3 items by default (center + 1 above + 1 below)
        // Items beyond that are hidden (opacity 0) and appear on scroll
        const absDistance = Math.abs(clampedDistance);
        let opacity = 0;
        if (absDistance <= 0.5) {
            opacity = 1; // Center item - full opacity
        } else if (absDistance <= 1.2) {
            opacity = 0.5; // Adjacent items (1 above, 1 below)
        } else if (absDistance <= 1.8) {
            // Transition zone - fade in/out as items scroll into view
            opacity = Math.max(0, 0.5 - (absDistance - 1.2) * 0.8);
        }
        // Items with absDistance > 1.8 stay at opacity 0 (hidden)

        const scale = 1 - Math.abs(clampedDistance) * 0.03;

        return {
            top: `${topPercent}%`,
            left: '0%', // All items start from same left alignment
            opacity: opacity,
            scale: Math.max(0.9, scale),
            rotation: rotation,
            zIndex: 10 - Math.abs(Math.round(clampedDistance))
        };
    };

    return (
        <section className="works-flow-section">
            <div className="works-flow-container max-w-1245">
                <div className="works-flow-content">
                    {/* Left Content */}
                    <div className="works-flow-left">
                        
                        <div className="text-content">
                            {subtitle && <p className="subtitle">{subtitle}</p>}
                            <h2 className="title">{title}</h2>
                        </div>
                    </div>

                    {/* Right Content - Timeline with Roles */}
                    <div className="works-flow-right">
                        <div className="timeline-container" ref={timelineRef}>
                            

                            <div className="center-indicator">
                                <div className="indicator-circle">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <rect x="4" y="11" width="16" height="2" rx="1"/>
                                    </svg>
                                </div>
                            </div>

                            <div
                                className={`roles-container ${isDragging ? 'dragging' : ''}`}
                                ref={rolesContainerRef}
                                onMouseDown={handleMouseDown}
                                onMouseMove={handleMouseMove}
                                onMouseUp={handleMouseUp}
                                onMouseLeave={handleMouseLeave}
                                onMouseEnter={handleMouseEnter}
                                onTouchStart={handleTouchStart}
                                onTouchMove={handleTouchMove}
                                onTouchEnd={handleTouchEnd}
                                onWheel={handleWheel}
                            >
                                {roles.map((role, index) => {
                                    const position = getRolePosition(index);
                                    const isActive = index === activeIndex;

                                    return (
                                        <div
                                            key={index}
                                            className={`role-item ${isActive ? 'active' : ''}`}
                                            style={{
                                                top: position.top,
                                                left: position.left,
                                                opacity: position.opacity,
                                                transform: `scale(${position.scale}) translateY(-50%) rotate(${position.rotation}deg)`,
                                                zIndex: position.zIndex,
                                            }}
                                            onClick={() => handleRoleClick(index)}
                                        >
                                            <span className="role-line"></span>
                                            <span className="role-name">{role.name}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Feature Cards - Changes based on active role */}
                <div className="feature-cards-wrapper">
                    <div className="feature-cards-header">
                        <span className="active-role-name">{roles[activeIndex]?.name}</span>
                    </div>
                    <div className="feature-cards" key={activeIndex}>
                        {roles[activeIndex]?.features.map((feature, index) => (
                            <div key={`${activeIndex}-${index}`} className="feature-card">
                                <h3 className="feature-title">{feature.title}</h3>
                                <p className="feature-description">{feature.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
};

export default WorksFlowSwiper;
