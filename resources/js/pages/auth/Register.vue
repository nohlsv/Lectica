<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import type { Program } from '@/types';
import { ref, computed } from 'vue';

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
    return [...new Set(props.programs.map(program => program.college))];
});

const selectedCollege = ref('');
const filteredPrograms = computed(() => {
    return selectedCollege.value
        ? props.programs.filter(program => program.college === selectedCollege.value)
        : props.programs;
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="user_role">Role</Label>
                    <select
                        id="user_role"
                        v-model="form.user_role"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                        :tabindex="1"
                    >
                        <option value="" disabled>Select your role</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty</option>
                    </select>
                    <InputError :message="form.errors.user_role" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="first_name">First Name</Label>
                        <Input
                            id="first_name"
                            type="text"
                            required
                            autofocus
                            :tabindex="2"
                            autocomplete="given-name"
                            v-model="form.first_name"
                            placeholder="First name"
                        />
                        <InputError :message="form.errors.first_name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="last_name">Last Name</Label>
                        <Input
                            id="last_name"
                            type="text"
                            required
                            :tabindex="3"
                            autocomplete="family-name"
                            v-model="form.last_name"
                            placeholder="Last name"
                        />
                        <InputError :message="form.errors.last_name" />
                    </div>
                </div>

                <div v-if="form.user_role === 'student'" class="grid gap-2">
                    <Label for="college">College</Label>
                    <select
                        id="college"
                        v-model="selectedCollege"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">All Colleges</option>
                        <option v-for="college in colleges" :key="college" :value="college">
                            {{ college }}
                        </option>
                    </select>
                </div>

                <div v-if="form.user_role === 'student'" class="grid gap-2">
                    <Label for="program">Program</Label>
                    <select
                        id="program"
                        v-model="form.program_id"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
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

                <div v-if="form.user_role === 'student'" class="grid gap-2">
                    <Label for="year_of_study">Year Level</Label>
                    <select
                        id="year_of_study"
                        v-model="form.year_of_study"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        required
                        :tabindex="5"
                    >
                        <option value="" disabled>Select your year level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="5th Year">5th Year</option>
                        <option value="Graduate">Graduate</option>
                    </select>
                    <InputError :message="form.errors.year_of_study" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="6"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="youremail@bpsu.edu.ph"
                    />
                    <div class="text-xs text-muted-foreground">
                        Only @bpsu.edu.ph email addresses are allowed
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="8"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="9" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="10">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
