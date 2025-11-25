import React from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { ExternalLink, AlertCircle } from 'lucide-react';

export default function ModuleIframe({ module, iframeUrl }) {
    const safeUrl = iframeUrl || (module?.config?.iframe_url ?? null);

    return (
        <AppLayout title={`${module?.name ?? 'Module'} (Embedded)`}>
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-bold text-gray-900">{module?.name ?? 'Module'}</h1>
                        <p className="mt-2 text-gray-600">Embedded integration via iframe (quick launch).</p>
                    </div>

                    {!safeUrl ? (
                        <div className="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg flex items-start">
                            <AlertCircle className="h-5 w-5 mt-0.5 mr-2" />
                            <div>
                                <p className="font-medium">Iframe URL not configured.</p>
                                <p className="text-sm mt-1">Ask admin to set <code>config.iframe_url</code> for this module.</p>
                            </div>
                        </div>
                    ) : (
                        <div className="bg-white shadow rounded-lg">
                            <div className="p-4 flex justify-between items-center border-b">
                                <div className="text-sm text-gray-600">
                                    <span className="font-medium">Source:</span> {safeUrl}
                                </div>
                                <a
                                    href={safeUrl}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center px-3 py-1.5 text-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Open in new tab
                                    <ExternalLink className="h-4 w-4 ml-1" />
                                </a>
                            </div>
                            <div className="aspect-[16/9]">
                                <iframe
                                    src={safeUrl}
                                    title={`${module?.name ?? 'Module'} iframe`}
                                    className="w-full h-[75vh]"
                                    frameBorder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    sandbox="allow-forms allow-scripts allow-same-origin allow-popups"
                                />
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}