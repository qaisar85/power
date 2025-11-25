import React from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { 
    ArrowRight, Users, Briefcase, FileText, Calendar, 
    Globe, Bell, TrendingUp, Building, MapPin, Package,
    Wallet, MessageSquare, CheckCircle, AlertCircle,
    Plus, Eye, Edit, Trash2, ShoppingCart, DollarSign,
    Truck, Wrench, Newspaper, Map, CreditCard, BarChart3, User
} from 'lucide-react';

export default function Dashboard({ modules, sectors, user, primaryRole, roles }) {
    // Common stats for all users
    const commonStats = [
        { name: 'Active Listings', value: '2.1M+', icon: Building, color: 'bg-blue-500' },
        { name: 'Job Opportunities', value: '450K+', icon: Users, color: 'bg-green-500' },
        { name: 'Active Tenders', value: '12K+', icon: FileText, color: 'bg-purple-500' },
        { name: 'Global Reach', value: '195+', icon: MapPin, color: 'bg-orange-500' },
    ];

    // Role-based quick actions
    const getRoleBasedActions = () => {
        const roleActions = {
            'Company': [
                { name: 'Post Listing', href: '/listings/new', icon: Plus, color: 'bg-blue-600', description: 'Create a new equipment listing' },
                { name: 'My Listings', href: '/account/listings', icon: Briefcase, color: 'bg-green-600', description: 'Manage your listings' },
                { name: 'Join Auction', href: '/auctions', icon: Calendar, color: 'bg-purple-600', description: 'Browse and bid on auctions' },
                { name: 'Wallet', href: '/packages', icon: Wallet, color: 'bg-orange-600', description: 'Manage your balance' },
            ],
            'Drilling Company': [
                { name: 'My Services', href: '/service-dashboard/services', icon: Wrench, color: 'bg-blue-600', description: 'Manage drilling services' },
                { name: 'Fleet Management', href: '/service-dashboard/drilling/rigs', icon: Truck, color: 'bg-green-600', description: 'Manage your rigs' },
                { name: 'Projects', href: '/service-dashboard/drilling/cases', icon: FileText, color: 'bg-purple-600', description: 'View completed projects' },
                { name: 'HSE Documents', href: '/service-dashboard/drilling/hse', icon: CheckCircle, color: 'bg-orange-600', description: 'Manage certificates' },
            ],
            'Inspection Company': [
                { name: 'My Services', href: '/service-dashboard/services', icon: Wrench, color: 'bg-blue-600', description: 'Manage inspection services' },
                { name: 'Incoming Requests', href: '/service-dashboard/requests', icon: Bell, color: 'bg-green-600', description: 'View service requests' },
                { name: 'Cases', href: '/service-dashboard/cases', icon: FileText, color: 'bg-purple-600', description: 'Completed work cases' },
                { name: 'Documents', href: '/service-dashboard/documents', icon: CheckCircle, color: 'bg-orange-600', description: 'Manage certificates' },
            ],
            'Logistics Company': [
                { name: 'My Routes', href: '/service-dashboard/logistics/routes', icon: Map, color: 'bg-blue-600', description: 'Manage transportation routes' },
                { name: 'Services', href: '/service-dashboard/logistics/services', icon: Truck, color: 'bg-green-600', description: 'Manage logistics services' },
                { name: 'Orders', href: '/service-dashboard/requests', icon: ShoppingCart, color: 'bg-purple-600', description: 'View orders' },
                { name: 'Calculator', href: '/logistics', icon: BarChart3, color: 'bg-orange-600', description: 'Cost calculator' },
            ],
            'Journalist': [
                { name: 'Create Article', href: '/news/create', icon: Plus, color: 'bg-blue-600', description: 'Publish news article' },
                { name: 'My Articles', href: '/news/my-articles', icon: Newspaper, color: 'bg-green-600', description: 'Manage your articles' },
                { name: 'Analytics', href: '/news/analytics', icon: BarChart3, color: 'bg-purple-600', description: 'View statistics' },
                { name: 'Earnings', href: '/news/earnings', icon: DollarSign, color: 'bg-orange-600', description: 'View payments' },
            ],
            'Branch': [
                { name: 'My Profile', href: '/regional-agent/profile', icon: User, color: 'bg-blue-600', description: 'Edit agent profile & upload video resume' },
                { name: 'Orders', href: '/branch/orders', icon: ShoppingCart, color: 'bg-green-600', description: 'View service orders' },
                { name: 'Services', href: '/branch/services', icon: Briefcase, color: 'bg-purple-600', description: 'Manage services' },
                { name: 'Earnings', href: '/branch/earnings', icon: DollarSign, color: 'bg-orange-600', description: 'View commissions' },
            ],
            'Investor': [
                { name: 'Browse Projects', href: '/investment/projects', icon: Eye, color: 'bg-blue-600', description: 'Find investment opportunities' },
                { name: 'My Investments', href: '/investor-dashboard', icon: TrendingUp, color: 'bg-green-600', description: 'View portfolio' },
                { name: 'Buy Shares', href: '/stocks/buy', icon: CreditCard, color: 'bg-purple-600', description: 'Purchase WOP shares' },
                { name: 'Market', href: '/stocks/market', icon: BarChart3, color: 'bg-orange-600', description: 'Secondary market' },
            ],
            'Freelancer': [
                { name: 'Create Service', href: '/freelance/services/new', icon: Plus, color: 'bg-blue-600', description: 'Offer a service' },
                { name: 'Browse Jobs', href: '/freelance/jobs', icon: Briefcase, color: 'bg-green-600', description: 'Find projects' },
                { name: 'My Services', href: '/freelance/services', icon: FileText, color: 'bg-purple-600', description: 'Manage services' },
                { name: 'Earnings', href: '/freelance/earnings', icon: DollarSign, color: 'bg-orange-600', description: 'View payments' },
            ],
        };

        return roleActions[primaryRole] || roleActions['Company'];
    };

    // Role-based widgets
    const getRoleBasedWidgets = () => {
        const widgets = {
            'Company': [
                { title: 'Active Listings', value: '0', icon: Briefcase, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Pending Requests', value: '0', icon: MessageSquare, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Active Packages', value: '0', icon: Package, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Wallet Balance', value: '$0.00', icon: Wallet, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Drilling Company': [
                { title: 'Active Services', value: '0', icon: Wrench, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Active Rigs', value: '0', icon: Truck, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Incoming Requests', value: '0', icon: Bell, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Completed Projects', value: '0', icon: CheckCircle, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Inspection Company': [
                { title: 'Active Services', value: '0', icon: Wrench, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Pending Requests', value: '0', icon: Bell, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Completed Cases', value: '0', icon: CheckCircle, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Valid Certificates', value: '0', icon: FileText, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Logistics Company': [
                { title: 'Active Routes', value: '0', icon: Map, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Active Orders', value: '0', icon: ShoppingCart, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Pending Requests', value: '0', icon: Bell, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Fleet Size', value: '0', icon: Truck, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Journalist': [
                { title: 'Published Articles', value: '0', icon: Newspaper, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Total Views', value: '0', icon: Eye, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Pending Articles', value: '0', icon: FileText, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Total Earnings', value: '$0.00', icon: DollarSign, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Branch': [
                { title: 'Active Orders', value: '0', icon: ShoppingCart, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Completed Services', value: '0', icon: CheckCircle, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Rating', value: '0.0', icon: TrendingUp, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Total Earnings', value: '$0.00', icon: DollarSign, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Investor': [
                { title: 'Active Investments', value: '0', icon: TrendingUp, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Total Invested', value: '$0.00', icon: DollarSign, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'WOP Shares', value: '0', icon: CreditCard, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'ROI', value: '0%', icon: BarChart3, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
            'Freelancer': [
                { title: 'Active Services', value: '0', icon: Briefcase, color: 'text-blue-600', bgColor: 'bg-blue-50' },
                { title: 'Active Projects', value: '0', icon: FileText, color: 'text-green-600', bgColor: 'bg-green-50' },
                { title: 'Applications', value: '0', icon: MessageSquare, color: 'text-purple-600', bgColor: 'bg-purple-50' },
                { title: 'Total Earnings', value: '$0.00', icon: DollarSign, color: 'text-orange-600', bgColor: 'bg-orange-50' },
            ],
        };

        return widgets[primaryRole] || widgets['Company'];
    };

    const roleBasedActions = getRoleBasedActions();
    const roleBasedWidgets = getRoleBasedWidgets();

    return (
        <AppLayout title="Dashboard">
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Welcome Section with Role Badge */}
                    <div className="mb-8">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-3xl font-bold text-gray-900">
                                    Welcome back, {user.name}!
                                </h1>
                                <p className="mt-2 text-gray-600">
                                    Your gateway to global business opportunities
                                </p>
                            </div>
                            {primaryRole && (
                                <div className="hidden sm:block">
                                    <span className="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                        {primaryRole}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Role-based Widgets */}
                    <div className="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        {roleBasedWidgets.map((widget) => (
                            <div key={widget.title} className="bg-white overflow-hidden shadow rounded-lg">
                                <div className="p-5">
                                    <div className="flex items-center">
                                        <div className="flex-shrink-0">
                                            <div className={`${widget.bgColor} p-3 rounded-md`}>
                                                <widget.icon className={`h-6 w-6 ${widget.color}`} />
                                            </div>
                                        </div>
                                        <div className="ml-5 w-0 flex-1">
                                            <dl>
                                                <dt className="text-sm font-medium text-gray-500 truncate">
                                                    {widget.title}
                                                </dt>
                                                <dd className="text-lg font-medium text-gray-900">
                                                    {widget.value}
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Role-based Quick Actions */}
                    <div className="mb-8">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {roleBasedActions.map((action) => (
                                <Link
                                    key={action.name}
                                    href={action.href}
                                    className="relative group bg-white p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 rounded-lg shadow hover:shadow-md transition-shadow"
                                >
                                    <div>
                                        <span className={`${action.color} rounded-lg inline-flex p-3 ring-4 ring-white`}>
                                            <action.icon className="h-6 w-6 text-white" />
                                        </span>
                                    </div>
                                    <div className="mt-4">
                                        <h3 className="text-lg font-medium text-gray-900">
                                            {action.name}
                                        </h3>
                                        <p className="mt-2 text-sm text-gray-500">
                                            {action.description}
                                        </p>
                                    </div>
                                    <span className="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400">
                                        <ArrowRight className="h-6 w-6" />
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </div>

                    {/* Common Platform Stats */}
                    <div className="mb-8">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">Platform Overview</h2>
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            {commonStats.map((stat) => (
                                <div key={stat.name} className="bg-white overflow-hidden shadow rounded-lg">
                                    <div className="p-5">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0">
                                                <div className={`${stat.color} p-3 rounded-md`}>
                                                    <stat.icon className="h-6 w-6 text-white" />
                                                </div>
                                            </div>
                                            <div className="ml-5 w-0 flex-1">
                                                <dl>
                                                    <dt className="text-sm font-medium text-gray-500 truncate">
                                                        {stat.name}
                                                    </dt>
                                                    <dd className="text-lg font-medium text-gray-900">
                                                        {stat.value}
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Notifications/Status Section */}
                    <div className="mb-8">
                        <div className="bg-white shadow rounded-lg p-6">
                            <div className="flex items-center">
                                <Bell className="h-5 w-5 text-gray-400 mr-3" />
                                <div className="flex-1">
                                    <h3 className="text-sm font-medium text-gray-900">Account Status</h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        {user.email_verified_at 
                                            ? 'Your email is verified. You\'re all set!' 
                                            : 'Please verify your email address to access all features.'}
                                    </p>
                                </div>
                                {!user.email_verified_at && (
                                    <Link
                                        href="/email/verification-notification"
                                        className="ml-4 text-sm text-indigo-600 hover:text-indigo-500"
                                    >
                                        Resend verification
                                    </Link>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Available Modules */}
                    {modules && modules.length > 0 && (
                        <div className="mb-8">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Platform Modules</h2>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {modules.slice(0, 6).map((module) => (
                                    <div key={module.id} className="bg-white shadow rounded-lg p-6">
                                        <div className="flex items-center">
                                            <div className="flex-shrink-0">
                                                <div className="bg-indigo-500 p-3 rounded-md">
                                                    <Globe className="h-6 w-6 text-white" />
                                                </div>
                                            </div>
                                            <div className="ml-4 flex-1">
                                                <h3 className="text-lg font-medium text-gray-900">
                                                    {module.name}
                                                </h3>
                                                <p className="text-sm text-gray-500">
                                                    {module.description}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="mt-4">
                                            {module.has_access ? (
                                                <Link
                                                    href={module.path || `/${module.slug}`}
                                                    className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                                >
                                                    Access Module
                                                    <ArrowRight className="ml-2 h-4 w-4" />
                                                </Link>
                                            ) : (
                                                <button className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                    Request Access
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
