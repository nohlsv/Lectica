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
    <div class="flex min-h-screen w-full flex-col items-center justify-center bg-lectica p-6 lg:p-8">
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <div class="flex items-center justify-between">

                <!-- Logo and Name on the left -->
                <!--<div class="flex items-center gap-2">-->
                    <!--<AppLogoIcon className="h-10 w-10" />-->
                    <!--<span class="text-xl font-bold">Lectica</span>-->
                <!--</div>-->

                <!-- Navigation on the right -->
                <!-- <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('home')"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Home
                    </Link> -->
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
                <!-- </nav> -->
            </div>
        </header>
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex items-center justify-center">

                <!-- fallback for the border in the middle
                 class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-center text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20
                 -->

                <!-- Container for the text-->
                 <div class=" rounded-2xl border-2 border-border bg-[#494242]/30 p-6 ">
                    <div
                    class=" text-center"
                    >
                    <div class="flex justify-center items-center logo-fall">
                        <img src="/lectica-logo.gif" alt="Lectica Logo" class="md:h-60 md:w-60 h-40 w-40 mb-5 animate-soft-bounce"/>
                    </div>
                        <div class="flex flex-col items-center justify-center mb-8">
                            <h1 class="text-6xl 2xl:text-6xl font-extrabold mb-2 animate-flow tracking-wide">Lectica</h1>
                            <p class="text-3xl md:text-4xl text-[#FFB347] pixel-outline tracking-wide">Review, Repeat, Retain</p>


                        <p class=" text-xl mt-3 text-[#FFF8DC] pixel-outline tracking-wide">
                            Your personal learning companion for efficient knowledge retention
                        </p>
                        </div>
                        <div v-if="!$page.props.auth.user" class="flex justify-center gap-4 mt-4 ">
                            <Link
                                :href="route('register')"
                                class="inline-block rounded-lg px-4 py-1 md:px-6 md:py-2 text-base md:text-lg font-medium bg-[#ff1640] hover:bg-[#cc1232] hover:scale-110 duration-300 pixel-outline border-[#0c0a03] border-2"
                            >
                                Get Started
                            </Link>
                            <Link
                                :href="route('login')"
                                class="inline-block rounded-lg px-4 py-1 md:px-6 md:py-2 text-base md:text-lg font-medium bg-[#ff6910] hover:bg-[#cc560d] hover:scale-110 duration-300 pixel-outline border-[#0c0a03] border-2"
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
