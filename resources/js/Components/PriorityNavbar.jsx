import React from 'react';
import { Link } from '@inertiajs/react';

export default function PriorityNavbar() {
    const priorityOne = [
        { name: 'Home', href: '/' },
        { name: 'Sale', href: '/sale' },
        { name: 'Rent', href: '/rent' },
        { name: 'Inspection company', href: '/inspection' },
        { name: 'Oil drilling company', href: '/oil-drilling' },
        { name: 'Logistics company', href: '/logistics' },
        { name: 'Tenders', href: '/tenders' },
        { name: 'Branch (regional managers)', href: '/branch' },
        { name: 'Contacts', href: '/contacts' },
    ];

    const priorityTwo = [
        { name: 'Business for sale', href: '/business-for-sale' },
        { name: 'Auction', href: '/auction' },
        { name: 'Stocks', href: '/stocks' },
        { name: 'Investment', href: '/investment' },
        { name: 'Job', href: '/jobs' },
        { name: 'Freelancer', href: '/freelancer' },
        { name: 'News', href: '/news' },
        { name: 'Partnership', href: '/partnership' },
        { name: 'Forum', href: '/forum' },
    ];

    return (
        <div className="bg-white border-b">
            {/* Priority 1 */}
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-2">
                <div className="flex items-center gap-3">
                    <span className="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-200">
                        GREEN – PRIORITY – MEEN 1
                    </span>
                    <nav className="flex-1 overflow-x-auto">
                        <ul className="flex items-center gap-4 text-sm">
                            {priorityOne.map((item) => (
                                <li key={item.name}>
                                    <Link href={item.href} className="text-gray-700 hover:text-green-700 hover:underline whitespace-nowrap">
                                        {item.name}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
            </div>

            {/* Priority 2 */}
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-2">
                <div className="flex items-center gap-3">
                    <span className="inline-flex items-center rounded-md bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700 ring-1 ring-inset ring-yellow-200">
                        YELLOW COLOUR – MEEN PRIORITY 2
                    </span>
                    <nav className="flex-1 overflow-x-auto">
                        <ul className="flex items-center gap-4 text-sm">
                            {priorityTwo.map((item) => (
                                <li key={item.name}>
                                    <Link href={item.href} className="text-gray-700 hover:text-yellow-700 hover:underline whitespace-nowrap">
                                        {item.name}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    );
}