import React, { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Search, MapPin, Clock, DollarSign, Briefcase, Users } from 'lucide-react';

export default function Jobs({ module, config }) {
    const [activeTab, setActiveTab] = useState('browse');

    const jobs = [
        {
            id: 1,
            title: 'Senior Software Engineer',
            company: 'TechCorp Solutions',
            location: 'Remote / New York',
            type: 'Full-time',
            salary: '$120k - $180k',
            posted: '2 days ago',
            description: 'Join our team to build next-generation software solutions...',
            skills: ['React', 'Node.js', 'Python', 'AWS']
        },
        {
            id: 2,
            title: 'Marketing Manager',
            company: 'Global Manufacturing Ltd',
            location: 'London, UK',
            type: 'Full-time',
            salary: '£45k - £65k',
            posted: '1 week ago',
            description: 'Lead our marketing initiatives across European markets...',
            skills: ['Digital Marketing', 'SEO', 'Analytics', 'Strategy']
        },
        {
            id: 3,
            title: 'Freelance Web Developer',
            company: 'Various Clients',
            location: 'Remote',
            type: 'Freelance',
            salary: '$50 - $100/hour',
            posted: '3 days ago',
            description: 'Multiple projects available for experienced developers...',
            skills: ['JavaScript', 'PHP', 'WordPress', 'E-commerce']
        }
    ];

    const tabs = [
        { id: 'browse', name: 'Browse Jobs', count: '450K+' },
        { id: 'freelance', name: 'Freelance', count: '125K+' },
        { id: 'posted', name: 'My Applications', count: '12' },
        { id: 'saved', name: 'Saved Jobs', count: '8' }
    ];

    return (
        <AppLayout title="Jobs Portal">
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Global Jobs Portal</h1>
                        <p className="mt-2 text-gray-600">
                            Find opportunities for professionals and freelancers worldwide
                        </p>
                    </div>

                    {/* Tabs */}
                    <div className="mb-8">
                        <div className="border-b border-gray-200">
                            <nav className="-mb-px flex space-x-8">
                                {tabs.map((tab) => (
                                    <button
                                        key={tab.id}
                                        onClick={() => setActiveTab(tab.id)}
                                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                            activeTab === tab.id
                                                ? 'border-indigo-500 text-indigo-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                        }`}
                                    >
                                        {tab.name}
                                        <span className="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs">
                                            {tab.count}
                                        </span>
                                    </button>
                                ))}
                            </nav>
                        </div>
                    </div>

                    {/* Search */}
                    <div className="mb-8 bg-white shadow rounded-lg p-6">
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-4">
                            <div className="lg:col-span-2">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        type="text"
                                        placeholder="Job title, keywords, or company"
                                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                            </div>
                            <div>
                                <div className="relative">
                                    <MapPin className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        type="text"
                                        placeholder="Location"
                                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                            </div>
                            <button className="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                                Search Jobs
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Job Listings */}
                        <div className="lg:col-span-2">
                            <div className="space-y-6">
                                {jobs.map((job) => (
                                    <div key={job.id} className="bg-white shadow rounded-lg p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <h3 className="text-lg font-medium text-gray-900">
                                                    {job.title}
                                                </h3>
                                                <p className="text-indigo-600 font-medium">{job.company}</p>
                                                <div className="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                                    <div className="flex items-center">
                                                        <MapPin className="h-4 w-4 mr-1" />
                                                        {job.location}
                                                    </div>
                                                    <div className="flex items-center">
                                                        <Briefcase className="h-4 w-4 mr-1" />
                                                        {job.type}
                                                    </div>
                                                    <div className="flex items-center">
                                                        <DollarSign className="h-4 w-4 mr-1" />
                                                        {job.salary}
                                                    </div>
                                                    <div className="flex items-center">
                                                        <Clock className="h-4 w-4 mr-1" />
                                                        {job.posted}
                                                    </div>
                                                </div>
                                                <p className="mt-3 text-gray-600">{job.description}</p>
                                                <div className="mt-3 flex flex-wrap gap-2">
                                                    {job.skills.map((skill) => (
                                                        <span
                                                            key={skill}
                                                            className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                                        >
                                                            {skill}
                                                        </span>
                                                    ))}
                                                </div>
                                            </div>
                                            <div className="ml-4 flex flex-col space-y-2">
                                                <button className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Apply Now
                                                </button>
                                                <button className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                    Save Job
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Quick Actions */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Quick Actions
                                </h3>
                                <div className="space-y-3">
                                    <button className="w-full text-left px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                                        Post a Job
                                    </button>
                                    <button className="w-full text-left px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                                        Upload Resume
                                    </button>
                                    <button className="w-full text-left px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                                        Create Profile
                                    </button>
                                </div>
                            </div>

                            {/* Job Categories */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Popular Categories
                                </h3>
                                <div className="space-y-3">
                                    {[
                                        'Technology', 'Healthcare', 'Finance', 'Marketing', 'Sales'
                                    ].map((category) => (
                                        <div key={category} className="flex items-center justify-between">
                                            <span className="text-sm text-gray-600">{category}</span>
                                            <span className="text-xs text-gray-400">
                                                {Math.floor(Math.random() * 50)}K+ jobs
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}