<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type Program, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    programs: Program[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
    program_id: user.program_id || '',
    year_of_study: user.year_of_study || '',
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name, email address, and academic details" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                class="mt-1 block w-full"
                                v-model="form.first_name"
                                required
                                autocomplete="given-name"
                                placeholder="First name"
                            />
                            <InputError class="mt-2" :message="form.errors.first_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                class="mt-1 block w-full"
                                v-model="form.last_name"
                                required
                                autocomplete="family-name"
                                placeholder="Last name"
                            />
                            <InputError class="mt-2" :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="program">Program</Label>
                        <select
                            id="program"
                            v-model="form.program_id"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                        >
                            <option value="" disabled>Select your program</option>
                            <option v-for="program in $props.programs" :key="program.id" :value="program.id">
                                {{ program.name }} ({{ program.code }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.program_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="year_of_study">Year Level</Label>
                        <select
                            id="year_of_study"
                            v-model="form.year_of_study"
                            class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                        >
                            <option value="" disabled>Select your year level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                            <option value="Graduate">Graduate</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.year_of_study" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="text-muted-foreground -mt-4 text-sm">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
