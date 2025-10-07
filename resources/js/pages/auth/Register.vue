<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import type { Program } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    programs: Program[];
}

const props = defineProps<Props>();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    program_id: '',
    year_of_study: '',
    password: '',
    password_confirmation: '',
    user_role: '',
});

const colleges = computed(() => {
    return [...new Set(props.programs.map((program) => program.college))];
});

const selectedCollege = ref('');
const filteredPrograms = computed(() => {
    return selectedCollege.value ? props.programs.filter((program) => program.college === selectedCollege.value) : props.programs;
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

const submit = () => {
    // Set default values for faculty
    if (form.user_role === 'faculty') {
        form.year_of_study = 'Graduate';
    }
    
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-red-950 to-gray-800">
        <Head title="Register" />
        
        <!-- Mobile-first responsive container -->
        <div class="flex min-h-screen items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
            <div class="w-full max-w-md space-y-8 sm:max-w-lg">
                <!-- Header -->
                <div class="text-center">
                    <Link :href="route('home')" class="mx-auto mb-6 block w-fit">
                        <img src="/lectica-logo.gif" alt="Lectica Logo" class="h-16 w-16 sm:h-20 sm:w-20" />
                    </Link>
                    <h2 class="text-2xl font-bold text-white sm:text-3xl">Create your account</h2>
                    <p class="mt-2 text-sm text-gray-300 sm:text-base">Enter your details below to get started</p>
                </div>

                <!-- Form Card -->
                <div class="rounded-xl bg-white/10 p-6 backdrop-blur-sm border border-red-300/30 shadow-2xl sm:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Role Selection -->
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-white sm:text-base" for="user_role">Role</Label>
                            <select
                                id="user_role"
                                v-model="form.user_role"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 sm:py-4 sm:text-base"
                                required
                                :tabindex="1"
                            >
                                <option value="" disabled>Select your role</option>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                            </select>
                            <InputError :message="form.errors.user_role" />
                        </div>

                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="first_name">First Name</Label>
                                <Input
                                    id="first_name"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="2"
                                    autocomplete="given-name"
                                    v-model="form.first_name"
                                    placeholder="First name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 sm:py-4 sm:text-base"
                                />
                                <InputError :message="form.errors.first_name" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="last_name">Last Name</Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    required
                                    :tabindex="3"
                                    autocomplete="family-name"
                                    v-model="form.last_name"
                                    placeholder="Last name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 sm:py-4 sm:text-base"
                                />
                                <InputError :message="form.errors.last_name" />
                            </div>
                        </div>

                        <!-- Student-specific fields -->
                        <div v-if="form.user_role === 'student'" class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="college">College</Label>
                                <select
                                    id="college"
                                    v-model="selectedCollege"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 sm:py-4 sm:text-base"
                                >
                                    <option value="">All Colleges</option>
                                    <option v-for="college in colleges" :key="college" :value="college">
                                        {{ college }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="program">Program</Label>
                                <select
                                    id="program"
                                    v-model="form.program_id"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:py-4 sm:text-base"
                                    required
                                    :tabindex="4"
                                >
                                    <option value="" disabled>Select your program</option>
                                    <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                                        {{ program.name }} ({{ program.code }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.program_id" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="year_of_study">Year Level</Label>
                                <select
                                    id="year_of_study"
                                    v-model="form.year_of_study"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:py-4 sm:text-base"
                                    required
                                    :tabindex="5"
                                >
                                    <option value="" disabled>Select your year level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="5th Year">5th Year</option>
                                </select>
                                <InputError :message="form.errors.year_of_study" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label class="text-sm font-medium text-white sm:text-base" for="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="6"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="youremail@bpsu.edu.ph"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:py-4 sm:text-base"
                            />
                            <div class="text-xs text-gray-300 sm:text-sm">Only @bpsu.edu.ph email addresses are allowed</div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Fields -->
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="password">Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        required
                                        :tabindex="7"
                                        autocomplete="new-password"
                                        v-model="form.password"
                                        placeholder="Password"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 pr-10 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:py-4 sm:text-base"
                                    />
                                    <button
                                        type="button"
                                        @click="togglePasswordVisibility"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        :tabindex="8"
                                    >
                                        <Eye v-if="!showPassword" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-white sm:text-base" for="password_confirmation">Confirm password</Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        required
                                        :tabindex="9"
                                        autocomplete="new-password"
                                        v-model="form.password_confirmation"
                                        placeholder="Confirm password"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-3 pr-10 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:py-4 sm:text-base"
                                    />
                                    <button
                                        type="button"
                                        @click="togglePasswordConfirmationVisibility"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none"
                                        :tabindex="10"
                                    >
                                        <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button 
                            type="submit" 
                            class="w-full rounded-lg bg-blue-600 py-3 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 sm:py-4 sm:text-base" 
                            tabindex="11" 
                            :disabled="form.processing"
                        >
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin sm:h-5 sm:w-5" />
                            Create account
                        </Button>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <span class="text-sm text-gray-300 sm:text-base">Already have an account? </span>
                        <TextLink 
                            :href="route('login')" 
                            class="text-sm text-blue-400 hover:text-blue-300 underline underline-offset-4 sm:text-base" 
                            :tabindex="12"
                        >
                            Log in
                        </TextLink>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
