<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const props = defineProps();
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:text-[#EDEDEC] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <div class="flex items-center justify-between">
                <!-- Logo and Name on the left -->
                <div class="flex items-center gap-2">
                    <AppLogoIcon className="h-10 w-10" />
                    <span class="text-xl font-bold">Lectica</span>
                </div>

                <!-- Navigation on the right -->
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('home')"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Home
                    </Link>
<!--                    <template v-else>-->
<!--                        <Link-->
<!--                            :href="route('login')"-->
<!--                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"-->
<!--                        >-->
<!--                            Log in-->
<!--                        </Link>-->
<!--                        <Link-->
<!--                            :href="route('register')"-->
<!--                            class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"-->
<!--                        >-->
<!--                            Register-->
<!--                        </Link>-->
<!--                    </template>-->
                </nav>
            </div>
        </header>
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-center text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20"
                >
                    <div class="flex flex-col items-center justify-center mb-8">
                        <img src="/android-chrome-512x512.png" alt="Lectica Logo" class="h-24 w-24 mb-4 object-contain" />
                        <h1 class="text-3xl font-bold mb-2">Lectica</h1>
                        <p class="text-xl font-medium text-primary">Review, Repeat, Retain</p>
                    </div>

                    <p class="mb-6 text-[#706f6c] dark:text-[#A1A09A] text-base">
                        Your personal learning companion for efficient knowledge retention
                    </p>

                    <div v-if="!$page.props.auth.user" class="flex justify-center gap-4 mt-4">
                        <Link
                            :href="route('register')"
                            class="inline-block rounded-md bg-primary px-6 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Get Started
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-block rounded-md border border-border px-6 py-2 text-sm font-medium hover:bg-accent/50"
                        >
                            Log In
                        </Link>
                    </div>
                </div>
            </main>
        </div>
        <div class="h-14.5 hidden lg:block"></div>
    </div>
</template>
