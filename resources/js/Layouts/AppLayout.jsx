import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { 
    Menu, X, User, Settings, LogOut, Bell, Search,
    Globe, Briefcase, Users, TrendingUp, FileText, Calendar
} from 'lucide-react';
import PriorityNavbar from '@/Components/PriorityNavbar';
import SiteFooter from '@/Components/SiteFooter';

export default function AppLayout({ children, title }) {
    const { auth, modules, disclaimer: globalDisclaimer } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const navigation = [
        { name: 'Dashboard', href: '/dashboard', icon: TrendingUp },
        { name: 'Marketplace', href: '/marketplace', icon: Briefcase },
        { name: 'Jobs Portal', href: '/jobs', icon: Users },
        { name: 'Tenders', href: '/tenders', icon: FileText },
        { name: 'Auctions', href: '/auctions', icon: Calendar },
        { name: 'Contacts', href: '/contacts', icon: Globe },
        { name: 'Training', href: '/training', icon: Globe },
        { name: 'News', href: '/news', icon: Bell },
    ];

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={title} />
            
            {/* Mobile sidebar */}
            <div className={`fixed inset-0 z-50 lg:hidden ${sidebarOpen ? 'block' : 'hidden'}`}>
                <div className="fixed inset-0 bg-gray-600 bg-opacity-75" onClick={() => setSidebarOpen(false)} />
                <div className="fixed inset-y-0 left-0 flex w-64 flex-col bg-white">
                    <div className="flex h-16 items-center justify-between px-4">
                        <h1 className="text-xl font-bold text-indigo-600">GlobalBiz</h1>
                        <button onClick={() => setSidebarOpen(false)}>
                            <X className="h-6 w-6" />
                        </button>
                    </div>
                    <nav className="flex-1 space-y-1 px-2 py-4">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            >
                                <item.icon className="mr-3 h-5 w-5" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                </div>
            </div>

            {/* Desktop sidebar */}
            <div className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
                <div className="flex flex-col flex-grow bg-white border-r border-gray-200">
                    <div className="flex h-16 items-center px-4">
                        <h1 className="text-xl font-bold text-indigo-600">GlobalBiz Platform</h1>
                    </div>
                    <nav className="flex-1 space-y-1 px-2 py-4">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            >
                                <item.icon className="mr-3 h-5 w-5" />
                                {item.name}
                            </Link>
                        ))}
                    </nav>
                </div>
            </div>

            {/* Main content */}
            <div className="lg:pl-64">
                {/* Top bar */}
                <div className="sticky top-0 z-40 flex h-16 bg-white shadow">
                    <button
                        className="px-4 text-gray-500 focus:outline-none lg:hidden"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <Menu className="h-6 w-6" />
                    </button>
                    
                    <div className="flex flex-1 justify-between px-4">
                        <div className="flex flex-1">
                            <div className="flex w-full md:ml-0">
                                <div className="relative w-full max-w-lg">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        className="block w-full rounded-md border-gray-300 pl-10 pr-3 py-2 placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Search businesses, jobs, tenders..."
                                        type="search"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <div className="ml-4 flex items-center md:ml-6">
                            <button className="rounded-full bg-white p-1 text-gray-400 hover:text-gray-500">
                                <Bell className="h-6 w-6" />
                            </button>
                            
                            <div className="relative ml-3">
                                <div className="flex items-center space-x-3">
                                    <span className="text-sm font-medium text-gray-700">{auth.user.name}</span>
                                    <img
                                        className="h-8 w-8 rounded-full"
                                        src={auth.user.profile_photo_url}
                                        alt={auth.user.name}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Priority navbar */}
                <PriorityNavbar />

                {/* Global disclaimer */}
                <div className="bg-yellow-50 border-y border-yellow-200 text-xs text-yellow-800 text-center px-4 py-2">
                    {globalDisclaimer || 'Investments involve risk. This is not a public offer.'}
                </div>

                {/* Page content */}
                <main className="flex-1">
                    {children}
                </main>

                {/* Footer */}
                <SiteFooter />
            </div>
        </div>
    );
}