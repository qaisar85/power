import React, { useEffect, useRef, useState } from 'react';

export default function GlobeComponent({ agents, onPointClick, onGlobeReady }) {
    const globeContainerRef = useRef(null);
    const [Globe, setGlobe] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Dynamic import to avoid SSR issues
        if (typeof window !== 'undefined') {
            import('react-globe.gl').then((module) => {
                setGlobe(() => module.default);
                setIsLoading(false);
                if (onGlobeReady) {
                    setTimeout(onGlobeReady, 100);
                }
            });
        }
    }, [onGlobeReady]);

    if (isLoading || !Globe) {
        return (
            <div className="w-full h-full flex items-center justify-center bg-gray-100">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading globe...</p>
                </div>
            </div>
        );
    }

    const pointsData = agents
        .filter(agent => agent.latitude && agent.longitude)
        .map((agent) => ({
            ...agent,
            lat: parseFloat(agent.latitude),
            lng: parseFloat(agent.longitude),
            size: 0.5,
            color: agent.performance_rating >= 4 ? '#10b981' : agent.performance_rating >= 3 ? '#f59e0b' : '#ef4444',
        }));

    return (
        <Globe
            globeImageUrl="//unpkg.com/three-globe/example/img/earth-blue-marble.jpg"
            backgroundImageUrl="//unpkg.com/three-globe/example/img/night-sky.png"
            pointsData={pointsData}
            pointColor="color"
            pointRadius="size"
            pointLabel={(point) => `
                <div style="padding: 8px; background: white; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <div style="font-weight: 600; margin-bottom: 4px;">${point.name || point.business_name || 'Agent'}</div>
                    <div style="font-size: 12px; color: #666;">${point.city || ''}, ${point.country || ''}</div>
                    ${point.performance_rating ? `<div style="font-size: 12px; color: #666;">Rating: ${point.performance_rating.toFixed(1)}</div>` : ''}
                </div>
            `}
            onPointClick={onPointClick}
            pointResolution={2}
            pointAltitude={0.01}
            pointMerge={true}
            width={globeContainerRef.current?.clientWidth || 800}
            height={600}
        />
    );
}

