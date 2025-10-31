@extends('tailadmin.layouts.app')

@section('title', 'Forms - ' . config('app.name'))

@section('content')
<div class="p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">Forms</h2>
        <nav>
            <ol class="flex items-center gap-2">
                <li><a class="font-medium text-gray-700 hover:text-brand-500 dark:text-gray-400" href="{{ route('tailadmin.dashboard') }}">Dashboard /</a></li>
                <li class="font-medium text-brand-500">Forms</li>
            </ol>
        </nav>
    </div>

    <!-- Contact Form -->
    <div class="rounded-lg border border-gray-200 bg-white p-7.5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-6 text-xl font-semibold text-gray-900 dark:text-white">Contact Form</h3>

        <form class="space-y-5">
            <!-- Name -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Full Name</label>
                <input
                    type="text"
                    placeholder="Enter your full name"
                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500"
                />
            </div>

            <!-- Email -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Email</label>
                <input
                    type="email"
                    placeholder="Enter your email"
                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500"
                />
            </div>

            <!-- Phone -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Phone Number</label>
                <input
                    type="tel"
                    placeholder="Enter your phone number"
                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500"
                />
            </div>

            <!-- Subject -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Subject</label>
                <select class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500">
                    <option value="">Select subject</option>
                    <option value="general">General Inquiry</option>
                    <option value="support">Technical Support</option>
                    <option value="sales">Sales</option>
                    <option value="feedback">Feedback</option>
                </select>
            </div>

            <!-- Message -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Message</label>
                <textarea
                    rows="6"
                    placeholder="Type your message"
                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none focus:border-brand-500 dark:border-gray-700 dark:text-white dark:focus:border-brand-500"
                ></textarea>
            </div>

            <!-- Checkbox -->
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    id="newsletter"
                    class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                />
                <label for="newsletter" class="text-sm text-gray-700 dark:text-gray-300">
                    Subscribe to newsletter
                </label>
            </div>

            <!-- Radio Buttons -->
            <div>
                <label class="mb-2.5 block text-sm font-medium text-gray-900 dark:text-white">Preferred Contact Method</label>
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            id="contact-email"
                            name="contact"
                            value="email"
                            class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500"
                            checked
                        />
                        <label for="contact-email" class="text-sm text-gray-700 dark:text-gray-300">Email</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="radio"
                            id="contact-phone"
                            name="contact"
                            value="phone"
                            class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500"
                        />
                        <label for="contact-phone" class="text-sm text-gray-700 dark:text-gray-300">Phone</label>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3">
                <button
                    type="submit"
                    class="rounded bg-brand-500 px-8 py-3 text-white hover:bg-brand-600"
                >
                    Send Message
                </button>
                <button
                    type="reset"
                    class="rounded border border-gray-300 px-8 py-3 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
