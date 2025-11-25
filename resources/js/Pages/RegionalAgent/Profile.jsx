import React, { useState, useEffect } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { 
    Upload, Video, MapPin, Building, Globe, Phone, Mail, 
    Clock, CheckCircle, AlertCircle, Save, X, Play, 
    Trash2, Plus, Loader2
} from 'lucide-react';

export default function RegionalAgentProfile({ 
    agent, 
    countries, 
    states: initialStates, 
    cities: initialCities,
    serviceTypeOptions 
}) {
    const [states, setStates] = useState(initialStates || []);
    const [cities, setCities] = useState(initialCities || []);
    const [logoPreview, setLogoPreview] = useState(agent.logo || null);
    const [videoPreview, setVideoPreview] = useState(agent.video_resume_url || null);
    const [loadingStates, setLoadingStates] = useState(false);
    const [loadingCities, setLoadingCities] = useState(false);

    const form = useForm({
        business_name: agent.business_name || '',
        business_description: agent.business_description || '',
        business_license: agent.business_license || '',
        region_type: agent.region_type || 'city',
        country_id: agent.country_id || '',
        state_id: agent.state_id || '',
        city_id: agent.city_id || '',
        latitude: agent.latitude || '',
        longitude: agent.longitude || '',
        service_types: agent.service_types || [],
        supported_categories: agent.supported_categories || [],
        languages: agent.languages || [],
        certifications: agent.certifications || [],
        office_address: agent.office_address || '',
        office_phone: agent.office_phone || '',
        office_email: agent.office_email || '',
        office_hours: agent.office_hours || {},
        working_hours: agent.working_hours || {},
        timezone: agent.timezone || '',
        logo: null,
        video_resume: null,
        commission_rate: agent.commission_rate || 10,
        service_fee: agent.service_fee || '',
    });

    // Load states when country changes
    useEffect(() => {
        if (form.data.country_id) {
            setLoadingStates(true);
            fetch(`/api/countries/${form.data.country_id}/states`)
                .then(response => response.json())
                .then(data => {
                    setStates(data || []);
                    setLoadingStates(false);
                    // Reset state and city when country changes
                    form.setData('state_id', '');
                    form.setData('city_id', '');
                    setCities([]);
                })
                .catch(() => {
                    setLoadingStates(false);
                });
        } else {
            setStates([]);
            setCities([]);
            form.setData('state_id', '');
            form.setData('city_id', '');
        }
    }, [form.data.country_id]);

    // Load cities when state changes
    useEffect(() => {
        if (form.data.state_id) {
            setLoadingCities(true);
            fetch(`/api/states/${form.data.state_id}/cities`)
                .then(response => response.json())
                .then(data => {
                    setCities(data || []);
                    setLoadingCities(false);
                    // Reset city when state changes
                    form.setData('city_id', '');
                })
                .catch(() => {
                    setLoadingCities(false);
                });
        } else {
            setCities([]);
            form.setData('city_id', '');
        }
    }, [form.data.state_id]);

    const handleLogoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.setData('logo', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setLogoPreview(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const handleVideoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            form.setData('video_resume', file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setVideoPreview(URL.createObjectURL(file));
            };
            reader.readAsDataURL(file);
        }
    };

    const removeLogo = () => {
        form.setData('logo', null);
        setLogoPreview(null);
    };

    const removeVideo = () => {
        form.setData('video_resume', null);
        setVideoPreview(null);
    };

    const addLanguage = () => {
        const lang = prompt('Enter language:');
        if (lang && !form.data.languages.includes(lang)) {
            form.setData('languages', [...form.data.languages, lang]);
        }
    };

    const removeLanguage = (index) => {
        form.setData('languages', form.data.languages.filter((_, i) => i !== index));
    };

    const toggleServiceType = (serviceType) => {
        const current = form.data.service_types;
        if (current.includes(serviceType)) {
            form.setData('service_types', current.filter(s => s !== serviceType));
        } else {
            form.setData('service_types', [...current, serviceType]);
        }
    };

    const submit = (e) => {
        e.preventDefault();
        form.put('/regional-agent/profile', {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    return (
        <AppLayout title="Regional Agent Profile">
            <Head title="Regional Agent Profile" />

            <div className="max-w-6xl mx-auto py-8 px-4">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-2">Regional Agent Profile</h1>
                    <p className="text-gray-600">
                        Manage your regional agent profile, upload video resume, and configure your services.
                    </p>
                    {!agent.is_verified && (
                        <div className="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-start gap-3">
                            <AlertCircle className="h-5 w-5 text-yellow-600 mt-0.5" />
                            <div>
                                <p className="text-sm font-medium text-yellow-800">Profile Pending Verification</p>
                                <p className="text-sm text-yellow-700 mt-1">
                                    Your profile is pending admin verification. Once verified, it will be visible on the contacts page.
                                </p>
                            </div>
                        </div>
                    )}
                    {agent.is_verified && (
                        <div className="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                            <CheckCircle className="h-5 w-5 text-green-600" />
                            <p className="text-sm font-medium text-green-800">Profile Verified ✓</p>
                        </div>
                    )}
                </div>

                <form onSubmit={submit} encType="multipart/form-data" className="space-y-8">
                    {/* Business Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <Building className="h-5 w-5" />
                            Business Information
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Business Name *
                                </label>
                                <input
                                    type="text"
                                    value={form.data.business_name}
                                    onChange={(e) => form.setData('business_name', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                />
                            </div>
                            <div className="md:col-span-2">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Business Description
                                </label>
                                <textarea
                                    value={form.data.business_description}
                                    onChange={(e) => form.setData('business_description', e.target.value)}
                                    rows={4}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Business License
                                </label>
                                <input
                                    type="text"
                                    value={form.data.business_license}
                                    onChange={(e) => form.setData('business_license', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Logo Upload */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6">Logo</h2>
                        <div className="flex items-start gap-6">
                            {logoPreview && (
                                <div className="relative">
                                    <img
                                        src={logoPreview}
                                        alt="Logo preview"
                                        className="w-32 h-32 object-cover rounded-lg border border-gray-300"
                                    />
                                    <button
                                        type="button"
                                        onClick={removeLogo}
                                        className="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600"
                                    >
                                        <X className="h-4 w-4" />
                                    </button>
                                </div>
                            )}
                            <div className="flex-1">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Logo (Max 5MB)
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={handleLogoChange}
                                    className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Video Resume Upload */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <Video className="h-5 w-5" />
                            Video Resume
                        </h2>
                        <div className="space-y-4">
                            {videoPreview && (
                                <div className="relative">
                                    <video
                                        src={videoPreview}
                                        controls
                                        className="w-full max-w-2xl rounded-lg border border-gray-300"
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                    <button
                                        type="button"
                                        onClick={removeVideo}
                                        className="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>
                            )}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Upload Video Resume (Max 100MB, MP4/WebM/OGG)
                                </label>
                                <input
                                    type="file"
                                    accept="video/mp4,video/webm,video/ogg"
                                    onChange={handleVideoChange}
                                    className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p className="mt-2 text-sm text-gray-500">
                                    Record a brief video introducing yourself and your services. This will be displayed on your public profile.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Location Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <MapPin className="h-5 w-5" />
                            Location & Coverage
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Region Type *
                                </label>
                                <select
                                    value={form.data.region_type}
                                    onChange={(e) => form.setData('region_type', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                    <option value="city">City</option>
                                    <option value="state">State/Province</option>
                                    <option value="country">Country</option>
                                    <option value="global">Global</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Country
                                </label>
                                <select
                                    value={form.data.country_id}
                                    onChange={(e) => form.setData('country_id', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    disabled={form.data.region_type === 'global'}
                                >
                                    <option value="">Select Country</option>
                                    {countries.map((country) => (
                                        <option key={country.id} value={country.id}>
                                            {country.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    State/Province
                                </label>
                                <select
                                    value={form.data.state_id}
                                    onChange={(e) => form.setData('state_id', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    disabled={!form.data.country_id || form.data.region_type === 'global' || form.data.region_type === 'country'}
                                >
                                    <option value="">Select State</option>
                                    {loadingStates ? (
                                        <option disabled>Loading...</option>
                                    ) : (
                                        states.map((state) => (
                                            <option key={state.id} value={state.id}>
                                                {state.name}
                                            </option>
                                        ))
                                    )}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    City
                                </label>
                                <select
                                    value={form.data.city_id}
                                    onChange={(e) => form.setData('city_id', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    disabled={!form.data.state_id || form.data.region_type === 'global' || form.data.region_type === 'country' || form.data.region_type === 'state'}
                                >
                                    <option value="">Select City</option>
                                    {loadingCities ? (
                                        <option disabled>Loading...</option>
                                    ) : (
                                        cities.map((city) => (
                                            <option key={city.id} value={city.id}>
                                                {city.name}
                                            </option>
                                        ))
                                    )}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Latitude
                                </label>
                                <input
                                    type="number"
                                    step="any"
                                    value={form.data.latitude}
                                    onChange={(e) => form.setData('latitude', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., 40.7128"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Longitude
                                </label>
                                <input
                                    type="number"
                                    step="any"
                                    value={form.data.longitude}
                                    onChange={(e) => form.setData('longitude', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., -74.0060"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Service Types */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6">Service Types</h2>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            {Object.entries(serviceTypeOptions).map(([key, label]) => (
                                <label
                                    key={key}
                                    className="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                >
                                    <input
                                        type="checkbox"
                                        checked={form.data.service_types.includes(key)}
                                        onChange={() => toggleServiceType(key)}
                                        className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <span className="text-sm">{label}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* Languages */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6">Languages</h2>
                        <div className="flex flex-wrap gap-2 mb-4">
                            {form.data.languages.map((lang, index) => (
                                <span
                                    key={index}
                                    className="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm"
                                >
                                    {lang}
                                    <button
                                        type="button"
                                        onClick={() => removeLanguage(index)}
                                        className="hover:text-red-600"
                                    >
                                        <X className="h-3 w-3" />
                                    </button>
                                </span>
                            ))}
                        </div>
                        <button
                            type="button"
                            onClick={addLanguage}
                            className="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            <Plus className="h-4 w-4" />
                            Add Language
                        </button>
                    </div>

                    {/* Contact Information */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2">
                            <Phone className="h-5 w-5" />
                            Contact Information
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Office Address
                                </label>
                                <textarea
                                    value={form.data.office_address}
                                    onChange={(e) => form.setData('office_address', e.target.value)}
                                    rows={3}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Office Phone
                                </label>
                                <input
                                    type="tel"
                                    value={form.data.office_phone}
                                    onChange={(e) => form.setData('office_phone', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Office Email
                                </label>
                                <input
                                    type="email"
                                    value={form.data.office_email}
                                    onChange={(e) => form.setData('office_email', e.target.value)}
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Timezone
                                </label>
                                <input
                                    type="text"
                                    value={form.data.timezone}
                                    onChange={(e) => form.setData('timezone', e.target.value)}
                                    placeholder="e.g., America/New_York"
                                    className="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Submit Button */}
                    <div className="flex items-center justify-end gap-4">
                        {form.errors && Object.keys(form.errors).length > 0 && (
                            <div className="flex-1 text-sm text-red-600">
                                Please fix the errors above.
                            </div>
                        )}
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {form.processing ? (
                                <>
                                    <Loader2 className="h-5 w-5 animate-spin" />
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <Save className="h-5 w-5" />
                                    Save Profile
                                </>
                            )}
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

