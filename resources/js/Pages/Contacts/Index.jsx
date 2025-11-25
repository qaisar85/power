import React, { useState, useRef, useEffect, useMemo } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import GlobeComponent from '@/Components/GlobeComponent';
import { Search, MapPin, Globe as GlobeIcon, Filter, X, Phone, Mail, Clock, Star, Play } from 'lucide-react';

export default function Contacts({ agents = [], countries = [], languages = [], filters = {}, stats = {} }) {
    const [searchTerm, setSearchTerm] = useState(filters.manager || '');
    const [selectedCountry, setSelectedCountry] = useState(filters.country || '');
    const [selectedLanguage, setSelectedLanguage] = useState(filters.language || '');
    const [selectedAgent, setSelectedAgent] = useState(null);

    // Filter agents based on search and filters
    const filteredAgents = useMemo(() => {
        return agents.filter((agent) => {
            if (searchTerm && !agent.name.toLowerCase().includes(searchTerm.toLowerCase()) && 
                !agent.business_name?.toLowerCase().includes(searchTerm.toLowerCase())) {
                return false;
            }
            if (selectedCountry && agent.country !== selectedCountry) {
                return false;
            }
            if (selectedLanguage && !agent.languages?.includes(selectedLanguage)) {
                return false;
            }
            return true;
        });
    }, [agents, searchTerm, selectedCountry, selectedLanguage]);

    // Apply filters to URL
    const applyFilters = () => {
        const params = {};
        if (searchTerm) params.manager = searchTerm;
        if (selectedCountry) params.country = selectedCountry;
        if (selectedLanguage) params.language = selectedLanguage;

        router.get('/contacts', params, {
            preserveState: true,
            replace: true,
        });
    };

    useEffect(() => {
        applyFilters();
    }, [searchTerm, selectedCountry, selectedLanguage]);

    // Handle globe point click
    const handlePointClick = (point) => {
        const agent = agents.find(a => a.id === point.id);
        if (agent) {
            setSelectedAgent(agent);
        }
    };

    // Reset filters
    const resetFilters = () => {
        setSearchTerm('');
        setSelectedCountry('');
        setSelectedLanguage('');
        setSelectedAgent(null);
        router.get('/contacts');
    };

    return (
        <AppLayout title="Contacts - Regional Managers">
            <Head title="Contacts - Regional Managers" />

            <div className="min-h-screen bg-gray-50">
                {/* Header Section */}
                <div className="bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-12">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <h1 className="text-4xl font-bold mb-4">Global Network of Regional Managers</h1>
                        <p className="text-xl text-indigo-100 max-w-3xl">
                            Connect with our verified regional representatives across 200+ countries. 
                            Find local support, services, and expertise in your region.
                        </p>
                        <div className="mt-6 flex items-center gap-6">
                            <div className="flex items-center gap-2">
                                <MapPin className="h-5 w-5" />
                                <span className="text-lg font-semibold">{stats.total_agents || 0} Active Agents</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <GlobeIcon className="h-5 w-5" />
                                <span className="text-lg font-semibold">{stats.total_countries || 0} Countries</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Filters Section */}
                <div className="bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                <input
                                    type="text"
                                    placeholder="Search by manager name..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <select
                                value={selectedCountry}
                                onChange={(e) => setSelectedCountry(e.target.value)}
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Countries</option>
                                {countries.map((country) => (
                                    <option key={country.id} value={country.name}>
                                        {country.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={selectedLanguage}
                                onChange={(e) => setSelectedLanguage(e.target.value)}
                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Languages</option>
                                {languages.map((lang) => (
                                    <option key={lang} value={lang}>
                                        {lang}
                                    </option>
                                ))}
                            </select>
                            <button
                                onClick={resetFilters}
                                className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2"
                            >
                                <X className="h-4 w-4" />
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                {/* Main Content: Globe + Agent List */}
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* 3D Globe Section */}
                        <div className="lg:col-span-2">
                            <div className="bg-white rounded-xl shadow-lg overflow-hidden" style={{ height: '600px' }}>
                                <GlobeComponent
                                    agents={filteredAgents}
                                    onPointClick={handlePointClick}
                                    onGlobeReady={() => setGlobeReady(true)}
                                />
                            </div>

                            {/* 2D Map Alternative (for mobile) */}
                            <div className="mt-4 lg:hidden">
                                <div className="bg-white rounded-lg shadow p-4">
                                    <p className="text-sm text-gray-600 mb-2">
                                        View on desktop for interactive 3D globe
                                    </p>
                                    <div className="grid grid-cols-2 gap-2">
                                        {filteredAgents.slice(0, 4).map((agent) => (
                                            <Link
                                                key={agent.id}
                                                href={`/contacts/${agent.id}`}
                                                className="p-3 border rounded-lg hover:bg-gray-50"
                                            >
                                                <div className="font-medium text-sm">{agent.name}</div>
                                                <div className="text-xs text-gray-500">{agent.city}, {agent.country}</div>
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Agent List Sidebar */}
                        <div className="space-y-4">
                            <div className="bg-white rounded-xl shadow-lg p-4">
                                <h2 className="text-lg font-semibold text-gray-900 mb-4">
                                    Regional Managers ({filteredAgents.length})
                                </h2>
                                <div className="space-y-3 max-h-[600px] overflow-y-auto">
                                    {filteredAgents.length === 0 ? (
                                        <div className="text-center py-8 text-gray-500">
                                            <MapPin className="h-12 w-12 mx-auto mb-2 text-gray-300" />
                                            <p>No agents found matching your filters</p>
                                        </div>
                                    ) : (
                                        filteredAgents.map((agent) => (
                                            <div
                                                key={agent.id}
                                                onClick={() => setSelectedAgent(agent)}
                                                className={`p-4 border rounded-lg cursor-pointer transition-all hover:shadow-md ${
                                                    selectedAgent?.id === agent.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'
                                                }`}
                                            >
                                                <div className="flex items-start gap-3">
                                                    {agent.logo ? (
                                                        <img
                                                            src={agent.logo}
                                                            alt={agent.name}
                                                            className="w-12 h-12 rounded-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <span className="text-indigo-600 font-semibold">
                                                                {agent.name.charAt(0).toUpperCase()}
                                                            </span>
                                                        </div>
                                                    )}
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2">
                                                            <h3 className="font-semibold text-gray-900 truncate">
                                                                {agent.name || agent.business_name}
                                                            </h3>
                                                            {agent.performance_rating && (
                                                                <div className="flex items-center gap-1">
                                                                    <Star className="h-4 w-4 text-yellow-400 fill-yellow-400" />
                                                                    <span className="text-sm text-gray-600">
                                                                        {agent.performance_rating.toFixed(1)}
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>
                                                        <div className="flex items-center gap-2 mt-1 text-sm text-gray-600">
                                                            <MapPin className="h-3 w-3" />
                                                            <span className="truncate">
                                                                {agent.city}, {agent.country}
                                                            </span>
                                                        </div>
                                                        {agent.languages && agent.languages.length > 0 && (
                                                            <div className="mt-2 flex flex-wrap gap-1">
                                                                {agent.languages.slice(0, 3).map((lang) => (
                                                                    <span
                                                                        key={lang}
                                                                        className="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded"
                                                                    >
                                                                        {lang}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Selected Agent Details Modal/Card */}
                    {selectedAgent && (
                        <div className="mt-8 bg-white rounded-xl shadow-lg p-6">
                            <div className="flex items-start justify-between mb-4">
                                <div className="flex items-start gap-4">
                                    {selectedAgent.logo ? (
                                        <img
                                            src={selectedAgent.logo}
                                            alt={selectedAgent.name}
                                            className="w-20 h-20 rounded-full object-cover"
                                        />
                                    ) : (
                                        <div className="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span className="text-2xl text-indigo-600 font-semibold">
                                                {selectedAgent.name.charAt(0).toUpperCase()}
                                            </span>
                                        </div>
                                    )}
                                    <div>
                                        <h2 className="text-2xl font-bold text-gray-900">
                                            {selectedAgent.name || selectedAgent.business_name}
                                        </h2>
                                        {selectedAgent.business_name && selectedAgent.name && (
                                            <p className="text-gray-600">{selectedAgent.business_name}</p>
                                        )}
                                        <div className="flex items-center gap-4 mt-2">
                                            <div className="flex items-center gap-1">
                                                <MapPin className="h-4 w-4 text-gray-400" />
                                                <span className="text-sm text-gray-600">
                                                    {selectedAgent.city}, {selectedAgent.country}
                                                </span>
                                            </div>
                                            {selectedAgent.performance_rating && (
                                                <div className="flex items-center gap-1">
                                                    <Star className="h-4 w-4 text-yellow-400 fill-yellow-400" />
                                                    <span className="text-sm font-medium">
                                                        {selectedAgent.performance_rating.toFixed(1)} Rating
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <button
                                    onClick={() => setSelectedAgent(null)}
                                    className="p-2 hover:bg-gray-100 rounded-full"
                                >
                                    <X className="h-5 w-5 text-gray-400" />
                                </button>
                            </div>

                            {/* Video Resume */}
                            {selectedAgent.video_resume_url && (
                                <div className="mb-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <Play className="h-5 w-5 text-indigo-600" />
                                        <h3 className="font-semibold text-gray-900">Video Presentation</h3>
                                    </div>
                                    <div className="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                        <video
                                            src={selectedAgent.video_resume_url}
                                            controls
                                            className="w-full h-full"
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </div>
                            )}

                            {/* Contact Information */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                {selectedAgent.office_phone && (
                                    <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <Phone className="h-5 w-5 text-indigo-600" />
                                        <div>
                                            <div className="text-xs text-gray-500">Phone</div>
                                            <div className="font-medium">{selectedAgent.office_phone}</div>
                                        </div>
                                    </div>
                                )}
                                {selectedAgent.office_email && (
                                    <div className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <Mail className="h-5 w-5 text-indigo-600" />
                                        <div>
                                            <div className="text-xs text-gray-500">Email</div>
                                            <div className="font-medium">{selectedAgent.office_email}</div>
                                        </div>
                                    </div>
                                )}
                                {selectedAgent.office_address && (
                                    <div className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg md:col-span-2">
                                        <MapPin className="h-5 w-5 text-indigo-600 mt-0.5" />
                                        <div>
                                            <div className="text-xs text-gray-500">Address</div>
                                            <div className="font-medium">{selectedAgent.office_address}</div>
                                        </div>
                                    </div>
                                )}
                                {selectedAgent.office_hours && (
                                    <div className="flex items-start gap-3 p-3 bg-gray-50 rounded-lg md:col-span-2">
                                        <Clock className="h-5 w-5 text-indigo-600 mt-0.5" />
                                        <div>
                                            <div className="text-xs text-gray-500">Working Hours</div>
                                            <div className="font-medium">
                                                {typeof selectedAgent.office_hours === 'object' 
                                                    ? JSON.stringify(selectedAgent.office_hours)
                                                    : selectedAgent.office_hours}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Languages and Services */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {selectedAgent.languages && selectedAgent.languages.length > 0 && (
                                    <div>
                                        <h4 className="font-semibold text-gray-900 mb-2">Languages</h4>
                                        <div className="flex flex-wrap gap-2">
                                            {selectedAgent.languages.map((lang) => (
                                                <span
                                                    key={lang}
                                                    className="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
                                                >
                                                    {lang}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )}
                                {selectedAgent.service_types && selectedAgent.service_types.length > 0 && (
                                    <div>
                                        <h4 className="font-semibold text-gray-900 mb-2">Services</h4>
                                        <div className="flex flex-wrap gap-2">
                                            {selectedAgent.service_types.map((service) => (
                                                <span
                                                    key={service}
                                                    className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm"
                                                >
                                                    {service}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Action Buttons */}
                            <div className="mt-6 flex gap-4">
                                <Link
                                    href={`/contacts/${selectedAgent.id}`}
                                    className="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 text-center font-medium"
                                >
                                    View Full Profile
                                </Link>
                                <button className="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                                    Contact Manager
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

