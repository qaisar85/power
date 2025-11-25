import React from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function ProjectShow({ project }) {
  return (
    <AppLayout title={project.title}>
      <div className="py-6">
        <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
          <div className="bg-white shadow rounded-lg p-6">
            <h1 className="text-2xl font-bold text-gray-900">{project.title}</h1>
            <div className="mt-2 text-gray-600">Category: {project.category || '—'}</div>
            <div className="mt-2 text-gray-600">Budget: {project.budget_min}–{project.budget_max} {project.currency} ({project.budget_type})</div>
            <div className="mt-2 text-gray-600">Deadline: {project.deadline_at || '—'}</div>
            <div className="mt-4 prose max-w-none">
              <p>{project.description || 'No description provided.'}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}