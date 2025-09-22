<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps();
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <!-- Overall background -->
    <div class="bg-lectica flex min-h-screen w-full flex-col items-center justify-center p-6 lg:p-8">
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <div class="flex items-center justify-between">
                <!-- Logo and Name on the left -->
                <!--<div class="flex items-center gap-2">-->
                <!--<AppLogoIcon className="h-10 w-10" />-->
                <!--<span class="text-xl font-bold">Lectica</span>-->
                <!--</div>-->

                <!-- Navigation on the right -->
            </div>
        </header>
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex items-center justify-center">
                <!-- fallback for the border in the middle
                 class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-center text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20
                 -->

                <!-- Container for the text-->
                <div class="border-border rounded-2xl border-2 bg-[#494242]/30 p-6">
                    <div class="text-center">
                        <nav class="flex items-center gap-4">
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('home')"
                                class="pixel-outline inline-block rounded-lg border-2 border-[#0c0a03] bg-[#ff6910] px-4 py-1 text-base font-medium duration-300 hover:scale-110 hover:bg-[#cc560d] md:px-6 md:py-2 md:text-lg mx-auto"
                            >
                                Back to dashboard
                            </Link>
                        </nav>
                        <div class="logo-fall flex items-center justify-center">
                            <img src="/lectica-logo.gif" alt="Lectica Logo" class="animate-soft-bounce mb-5 h-40 w-40 md:h-60 md:w-60" />
                        </div>
                        <div class="mb-8 flex flex-col items-center justify-center">
                            <h1 class="animate-flow mb-2 text-6xl font-extrabold tracking-wide 2xl:text-6xl">Lectica</h1>
                            <p class="pixel-outline text-3xl tracking-wide text-[#FFB347] md:text-4xl">Review, Repeat, Retain</p>

                            <p class="pixel-outline mt-3 text-xl tracking-wide text-[#FFF8DC]">
                                Your personal learning companion for efficient knowledge retention
                            </p>
                        </div>
                        <div v-if="!$page.props.auth.user" class="mt-4 flex justify-center gap-4">
                            <Link
                                :href="route('register')"
                                class="pixel-outline inline-block rounded-lg border-2 border-[#0c0a03] bg-[#ff1640] px-4 py-1 text-base font-medium duration-300 hover:scale-110 hover:bg-[#cc1232] md:px-6 md:py-2 md:text-lg"
                            >
                                Get Started
                            </Link>
                            <Link
                                :href="route('login')"
                                class="pixel-outline inline-block rounded-lg border-2 border-[#0c0a03] bg-[#ff6910] px-4 py-1 text-base font-medium duration-300 hover:scale-110 hover:bg-[#cc560d] md:px-6 md:py-2 md:text-lg"
                            >
                                Log In
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
