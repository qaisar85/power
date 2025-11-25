import React, { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { Briefcase, ShoppingCart, FileText, MessageSquare, Star, PlusCircle, Search, Globe, Shield } from 'lucide-react';

export default function Freelance({ module, config }) {
  const [activeTab, setActiveTab] = useState('services'); // services | projects

  const tabs = [
    { id: 'services', name: 'Browse Services', icon: <ShoppingCart className="h-4 w-4 mr-1" /> },
    { id: 'projects', name: 'Browse Projects', icon: <FileText className="h-4 w-4 mr-1" /> },
  ];

  return (
    <AppLayout title="Freelance">
      <div className="py-6">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {/* Header */}
          <div className="mb-6 flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Freelance Marketplace</h1>
              <p className="mt-1 text-gray-600">Offer services or post projects in oil & gas</p>
            </div>
            <div className="flex gap-2">
              <Link
                href={route('freelance.services.create')}
                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
              >
                <PlusCircle className="h-4 w-4 mr-2" /> Post a Service
              </Link>
              <Link
                href={route('freelance.projects.create')}
                className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                <FileText className="h-4 w-4 mr-2" /> Post a Project
              </Link>
            </div>
          </div>

          {/* Tabs */}
          <div className="border-b border-gray-200 mb-6">
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
                  <span className="inline-flex items-center">{tab.icon}{tab.name}</span>
                </button>
              ))}
            </nav>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Main */}
            <div className="lg:col-span-2">
              {/* Search & Filters */}
              <div className="bg-white shadow rounded-lg p-4 mb-4">
                <div className="flex gap-3">
                  <div className="flex-1">
                    <div className="relative">
                      <Search className="h-4 w-4 text-gray-400 absolute left-3 top-3" />
                      <input
                        type="text"
                        placeholder={activeTab === 'services' ? 'Search services…' : 'Search projects…'}
                        className="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"
                      />
                    </div>
                  </div>
                  <select className="px-3 py-2 border border-gray-300 rounded-md">
                    <option>All categories</option>
                    <option>Drilling</option>
                    <option>Logistics</option>
                    <option>Engineering</option>
                    <option>HSE</option>
                  </select>
                  <select className="px-3 py-2 border border-gray-300 rounded-md">
                    {activeTab === 'services' ? (
                      <>
                        <option>Fixed price</option>
                        <option>Hourly</option>
                        <option>Package</option>
                      </>
                    ) : (
                      <>
                        <option>Any budget</option>
                        <option>Fixed</option>
                        <option>Hourly</option>
                      </>
                    )}
                  </select>
                </div>
              </div>

              {/* Results (placeholder grid) */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {[...Array(6)].map((_, idx) => (
                  <div key={idx} className="bg-white shadow rounded-lg p-4">
                    <div className="flex items-center justify-between">
                      <h3 className="text-lg font-medium text-gray-900">
                        {activeTab === 'services' ? 'Expert seismic data analysis' : '3D reservoir modeling for field X'}
                      </h3>
                      <div className="flex items-center text-sm text-gray-500">
                        <Star className="h-4 w-4 text-yellow-500 mr-1" /> 4.{(7 + (idx % 3))}
                      </div>
                    </div>
                    <p className="mt-2 text-gray-600">
                      {activeTab === 'services'
                        ? 'Deliverable includes detailed report, maps, and recommendations.'
                        : 'Need a comprehensive model and uncertainty analysis within 30 days.'}
                    </p>
                    <div className="mt-3 flex items-center justify-between text-sm text-gray-700">
                      <div className="flex items-center gap-2">
                        <Globe className="h-4 w-4" /> Remote
                        <Shield className="h-4 w-4" /> Escrow
                      </div>
                      <div className="font-semibold">
                        {activeTab === 'services' ? '$800 • Fixed' : '$5,000 • Budget'}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Sidebar */}
            <div className="space-y-6">
              <div className="bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-3">Quick Actions</h3>
                <div className="space-y-3">
                  <Link href={route('freelance.services.create')} className="block w-full text-left px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    Offer a Service
                  </Link>
                  <Link href={route('freelance.projects.create')} className="block w-full text-left px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    Post a Project
                  </Link>
                </div>
              </div>
              <div className="bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-3">How it works</h3>
                <ul className="text-sm text-gray-600 space-y-2 list-disc pl-5">
                  <li>Freelancers publish services with clear scope and price</li>
                  <li>Employers post projects with budget and timeline</li>
                  <li>All communication and payments happen in your account</li>
                  <li>Moderation ensures quality and compliance</li>
                </ul>
              </div>
              <div className="bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-3">Safety & Ratings</h3>
                <ul className="text-sm text-gray-600 space-y-2 list-disc pl-5">
                  <li>Escrow-based payments for transaction security</li>
                  <li>Ratings and reviews influence search ranking</li>
                  <li>Verified profiles for companies and individuals</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}