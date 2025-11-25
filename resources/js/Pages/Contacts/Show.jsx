import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { MapPin, Phone, Mail, Clock, Star, Play, Globe, ArrowLeft, CheckCircle } from 'lucide-react';

export default function ContactsShow({ agent }) {
    if (!agent) {
        return (
            <AppLayout title="Agent Not Found">
                <div className="py-12 text-center">
                    <p className="text-gray-600">Agent not found.</p>
                    <Link href="/contacts" className="text-indigo-600 hover:text-indigo-500 mt-4 inline-block">
                        ← Back to Contacts
                    </Link>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout title={`${agent.name} - Regional Manager`}>
            <Head title={`${agent.name} - Regional Manager`} />

            <div className="min-h-screen bg-gray-50 py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Back Button */}
                    <Link
                        href="/contacts"
                        className="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Back to Contacts
                    </Link>

                    {/* Agent Header */}
                    <div className="bg-white rounded-xl shadow-lg p-8 mb-8">
                        <div className="flex flex-col md:flex-row gap-6">
                            {/* Profile Image/Logo */}
                            <div className="flex-shrink-0">
                                {agent.logo ? (
                                    <img
                                        src={agent.logo}
                                        alt={agent.name}
                                        className="w-32 h-32 rounded-full object-cover border-4 border-indigo-100"
                                    />
                                ) : (
                                    <div className="w-32 h-32 rounded-full bg-indigo-100 flex items-center justify-center border-4 border-indigo-200">
                                        <span className="text-4xl text-indigo-600 font-bold">
                                            {agent.name.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                )}
                            </div>

                            {/* Agent Info */}
                            <div className="flex-1">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <h1 className="text-3xl font-bold text-gray-900 mb-2">
                                            {agent.name || agent.business_name}
                                        </h1>
                                        {agent.business_name && agent.name && (
                                            <p className="text-lg text-gray-600 mb-4">{agent.business_name}</p>
                                        )}
                                        <div className="flex items-center gap-4 mb-4">
                                            <div className="flex items-center gap-2">
                                                <MapPin className="h-5 w-5 text-gray-400" />
                                                <span className="text-gray-600">
                                                    {agent.city}, {agent.state}, {agent.country}
                                                </span>
                                            </div>
                                            {agent.performance_rating && (
                                                <div className="flex items-center gap-1">
                                                    <Star className="h-5 w-5 text-yellow-400 fill-yellow-400" />
                                                    <span className="font-semibold text-gray-900">
                                                        {agent.performance_rating.toFixed(1)}
                                                    </span>
                                                    <span className="text-gray-500 text-sm">
                                                        ({agent.total_services_completed || 0} services)
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                        <CheckCircle className="h-4 w-4" />
                                        Verified
                                    </div>
                                </div>

                                {agent.business_description && (
                                    <p className="text-gray-700 leading-relaxed mb-6">
                                        {agent.business_description}
                                    </p>
                                )}

                                {/* Contact Buttons */}
                                <div className="flex flex-wrap gap-4">
                                    {agent.office_phone && (
                                        <a
                                            href={`tel:${agent.office_phone}`}
                                            className="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                        >
                                            <Phone className="h-4 w-4" />
                                            Call
                                        </a>
                                    )}
                                    {agent.office_email && (
                                        <a
                                            href={`mailto:${agent.office_email}`}
                                            className="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                        >
                                            <Mail className="h-4 w-4" />
                                            Email
                                        </a>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Video Resume Section */}
                    {agent.video_resume_url && (
                        <div className="bg-white rounded-xl shadow-lg p-8 mb-8">
                            <div className="flex items-center gap-3 mb-4">
                                <Play className="h-6 w-6 text-indigo-600" />
                                <h2 className="text-2xl font-bold text-gray-900">Video Presentation</h2>
                            </div>
                            <div className="aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                <video
                                    src={agent.video_resume_url}
                                    controls
                                    className="w-full h-full"
                                    poster={agent.logo}
                                >
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <p className="mt-4 text-sm text-gray-600">
                                This representative introduces themselves and explains the services they can provide in their region.
                            </p>
                        </div>
                    )}

                    {/* Contact Information Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        {/* Office Address */}
                        {agent.office_address && (
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <div className="flex items-center gap-3 mb-4">
                                    <MapPin className="h-6 w-6 text-indigo-600" />
                                    <h3 className="text-xl font-semibold text-gray-900">Office Address</h3>
                                </div>
                                <p className="text-gray-700">{agent.office_address}</p>
                                {agent.latitude && agent.longitude && (
                                    <div className="mt-4">
                                        <a
                                            href={`https://www.google.com/maps?q=${agent.latitude},${agent.longitude}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-indigo-600 hover:text-indigo-700 text-sm"
                                        >
                                            View on Google Maps →
                                        </a>
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Contact Details */}
                        <div className="bg-white rounded-xl shadow-lg p-6">
                            <h3 className="text-xl font-semibold text-gray-900 mb-4">Contact Information</h3>
                            <div className="space-y-4">
                                {agent.office_phone && (
                                    <div className="flex items-center gap-3">
                                        <Phone className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div className="text-sm text-gray-500">Phone</div>
                                            <a href={`tel:${agent.office_phone}`} className="font-medium text-gray-900">
                                                {agent.office_phone}
                                            </a>
                                        </div>
                                    </div>
                                )}
                                {agent.office_email && (
                                    <div className="flex items-center gap-3">
                                        <Mail className="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div className="text-sm text-gray-500">Email</div>
                                            <a href={`mailto:${agent.office_email}`} className="font-medium text-gray-900">
                                                {agent.office_email}
                                            </a>
                                        </div>
                                    </div>
                                )}
                                {agent.office_hours && (
                                    <div className="flex items-start gap-3">
                                        <Clock className="h-5 w-5 text-gray-400 mt-0.5" />
                                        <div>
                                            <div className="text-sm text-gray-500">Working Hours</div>
                                            <div className="font-medium text-gray-900">
                                                {typeof agent.office_hours === 'object' 
                                                    ? JSON.stringify(agent.office_hours, null, 2)
                                                    : agent.office_hours}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Languages */}
                        {agent.languages && agent.languages.length > 0 && (
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <div className="flex items-center gap-3 mb-4">
                                    <Globe className="h-6 w-6 text-indigo-600" />
                                    <h3 className="text-xl font-semibold text-gray-900">Languages</h3>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {agent.languages.map((lang) => (
                                        <span
                                            key={lang}
                                            className="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full font-medium"
                                        >
                                            {lang}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Services */}
                        {agent.service_types && agent.service_types.length > 0 && (
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <h3 className="text-xl font-semibold text-gray-900 mb-4">Services Offered</h3>
                                <div className="space-y-2">
                                    {agent.service_types.map((service) => (
                                        <div key={service} className="flex items-center gap-2">
                                            <CheckCircle className="h-5 w-5 text-green-500" />
                                            <span className="text-gray-700">{service}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Region Coverage */}
                    <div className="bg-white rounded-xl shadow-lg p-8 mb-8">
                        <h3 className="text-xl font-semibold text-gray-900 mb-4">Region Coverage</h3>
                        <p className="text-gray-700 mb-4">{agent.region_coverage || 'Global Coverage'}</p>
                        {agent.latitude && agent.longitude && (
                            <div className="mt-4 h-64 bg-gray-100 rounded-lg overflow-hidden">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    frameBorder="0"
                                    style={{ border: 0 }}
                                    src={`https://www.google.com/maps/embed/v1/place?key=YOUR_GOOGLE_MAPS_API_KEY&q=${agent.latitude},${agent.longitude}`}
                                    allowFullScreen
                                ></iframe>
                            </div>
                        )}
                    </div>

                    {/* Reviews Section */}
                    {agent.reviews && agent.reviews.length > 0 && (
                        <div className="bg-white rounded-xl shadow-lg p-8">
                            <h3 className="text-xl font-semibold text-gray-900 mb-6">Client Reviews</h3>
                            <div className="space-y-4">
                                {agent.reviews.map((review) => (
                                    <div key={review.id} className="border-b border-gray-200 pb-4 last:border-0">
                                        <div className="flex items-center gap-2 mb-2">
                                            <div className="flex">
                                                {[...Array(5)].map((_, i) => (
                                                    <Star
                                                        key={i}
                                                        className={`h-4 w-4 ${
                                                            i < review.rating
                                                                ? 'text-yellow-400 fill-yellow-400'
                                                                : 'text-gray-300'
                                                        }`}
                                                    />
                                                ))}
                                            </div>
                                            <span className="text-sm text-gray-500">
                                                {review.user_name} • {new Date(review.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        {review.comment && (
                                            <p className="text-gray-700">{review.comment}</p>
                                        )}
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

