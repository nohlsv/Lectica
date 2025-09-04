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
    <div
        class="flex min-h-screen w-full flex-col items-center justify-center bg-[#FDFDFC] bg-[url(https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/64c9ea21-82be-48a3-a6f5-6d1786a3fc7e/d849dwo-0cdfe0ca-4bbe-43d5-b8d4-6c58db906e1e.gif?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7InBhdGgiOiJcL2ZcLzY0YzllYTIxLTgyYmUtNDhhMy1hNmY1LTZkMTc4NmEzZmM3ZVwvZDg0OWR3by0wY2RmZTBjYS00YmJlLTQzZDUtYjhkNC02YzU4ZGI5MDZlMWUuZ2lmIn1dXSwiYXVkIjpbInVybjpzZXJ2aWNlOmZpbGUuZG93bmxvYWQiXX0.n3jDMgOwazieu5EgWAGyGW4kZSx12Xqbs3BMHPLp6o4)] bg-cover bg-center p-6 lg:p-8"
    >
        <header class="mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <div class="flex items-center justify-between">
                <!-- Logo and Name on the left -->
                <!--<div class="flex items-center gap-2">-->
                <!--<AppLogoIcon className="h-10 w-10" />-->
                <!--<span class="text-xl font-bold">Lectica</span>-->
                <!--</div>-->

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
        <div class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
            <main class="flex items-center justify-center">
                <!-- fallback for the border in the middle
                 class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-center text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20
                 -->

                <!-- Container for the text-->
                <div class="flex-1 text-center">
                    <div class="mb-8 flex flex-col items-center justify-center">
                        <img src="/android-chrome-512x512.png" alt="Lectica Logo" class="mb-4 h-50 w-50 object-contain" />
                        <h1
                            class="text-primary mb-2 text-3xl font-bold dark:bg-gradient-to-r dark:from-[#fe1d41] dark:to-[#fe7c16] dark:bg-clip-text dark:text-transparent"
                        >
                            Lectica
                        </h1>
                        <p class="text-primary text-4xl">Review, Repeat, Retain</p>
                    </div>

                    <p class="mb-6 text-lg text-[#706f6c] dark:text-[#bebdb8]">Your personal learning companion for efficient knowledge retention</p>

                    <div v-if="!$page.props.auth.user" class="mt-4 flex justify-center gap-4">
                        <Link
                            :href="route('register')"
                            class="bg-primary inline-block rounded-md px-6 py-2 text-sm font-medium text-[#FDFDFC] dark:bg-[#3aa035] dark:hover:bg-[#66b562]"
                        >
                            Get Started
                        </Link>
                        <Link
                            :href="route('login')"
                            class="hover:bg-accent/70 inline-block rounded-md border border-[#FDFDFC] px-6 py-2 text-sm font-medium"
                        >
                            Log In
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
