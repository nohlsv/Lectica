<template>
    <Head title="Multiplayer Games" />

    <AppLayout>
        <div class="bg-gradient min-h-screen py-6 sm:py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tab Navigation -->
                <div class="mx-2 mb-4 sm:mx-4 sm:mb-6">
                    <nav class="flex flex-col gap-1 sm:flex-row sm:gap-0 sm:space-x-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'lobby'"
                            :class="[
                                'border-b-2 px-3 py-3 text-sm font-medium whitespace-nowrap transition-colors sm:px-1 sm:py-2 sm:text-sm md:text-base',
                                activeTab === 'lobby'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            Join Games ({{ waitingGames.data.length }})
                        </button>
                        <button
                            @click="activeTab = 'create'"
                            :class="[
                                'border-b-2 px-3 py-3 text-sm font-medium whitespace-nowrap transition-colors sm:px-1 sm:py-2 sm:text-sm md:text-base',
                                activeTab === 'create'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            Create Game
                        </button>
                        <button
                            @click="activeTab = 'mygames'"
                            :class="[
                                'border-b-2 px-3 py-3 text-sm font-medium whitespace-nowrap transition-colors sm:px-1 sm:py-2 sm:text-sm md:text-base',
                                activeTab === 'mygames'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            ]"
                        >
                            My Games ({{ myGames.data.length }})
                        </button>
                    </nav>
                </div>

                <!-- Join Games Tab -->
                <div v-show="activeTab === 'lobby'" class="bg-container mx-2 overflow-hidden shadow-sm sm:mx-4 sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">
                            <h3 class="pixel-outline text-lg font-medium text-gray-100 sm:text-xl md:text-2xl">
                                Available Games ({{ waitingGames.data.length }} waiting)
                            </h3>

                            <!-- Join by Code Button -->
                            <button
                                @click="showJoinByCode = !showJoinByCode"
                                class="rounded-md bg-blue-600 px-4 py-3 text-sm text-white transition-colors hover:bg-blue-700 sm:px-4 sm:py-2 sm:text-sm md:text-base"
                            >
                                Join by Code
                            </button>
                        </div>

                        <!-- Join by Code Form -->
                        <div v-if="showJoinByCode" class="mb-6 rounded-lg border-2 border-blue-500 bg-black/50 p-4">
                            <h4 class="text-md pixel-outline mb-3 font-medium text-blue-300">Join Private Game</h4>
                            <form @submit.prevent="joinByCode" class="flex space-x-3">
                                <input
                                    v-model="gameCodeForm.game_code"
                                    type="text"
                                    placeholder="Enter game code"
                                    class="flex-1 rounded-md border-gray-300 bg-gray-900 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500"
                                    maxlength="8"
                                />
                                <button
                                    type="submit"
                                    :disabled="!gameCodeForm.game_code || gameCodeForm.processing"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Join
                                </button>
                            </form>
                            <div v-if="gameCodeForm.errors.game_code" class="mt-2 text-sm text-red-400">
                                {{ gameCodeForm.errors.game_code }}
                            </div>
                        </div>

                        <!-- Games Grid -->
                        <div v-if="waitingGames.data.length > 0" class="grid grid-cols-1 gap-3 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="game in waitingGames.data"
                                :key="game.id"
                                class="rounded-lg border-2 border-green-500 bg-black/50 p-3 transition-all hover:shadow-md sm:p-4"
                            >
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="pixel-outline inline-flex items-center rounded-full bg-red-900/20 px-2.5 py-0.5 text-xs font-medium text-red-300"
                                    >
                                        PvP
                                    </span>
                                    <span class="pixel-outline text-xs text-gray-400">{{ formatTimeAgo(game.created_at) }}</span>
                                </div>

                                <!-- Creator Info -->
                                <div class="mb-3 flex items-center">
                                    <div class="mr-3 flex h-10 w-10 items-center justify-center rounded-full border-2 border-green-500 bg-black/50 sm:h-8 sm:w-8">
                                        <span class="pixel-outline text-sm font-bold text-green-300 sm:text-sm">
                                            {{ getInitials(game.player_one.first_name + ' ' + game.player_one.last_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="pixel-outline text-base font-medium text-green-300 sm:text-sm md:text-base">
                                            {{ game.player_one.first_name }} {{ game.player_one.last_name }}
                                        </p>
                                        <p class="pixel-outline text-sm text-gray-400 sm:text-xs">Game Creator</p>
                                    </div>
                                </div>

                                <!-- Source Info -->
                                <div class="mb-3">
                                    <p class="pixel-outline text-base font-medium text-gray-100 sm:text-sm md:text-base">
                                        {{ game.file ? game.file.title || game.file.name : game.collection?.name }}
                                    </p>
                                    <p class="pixel-outline text-sm text-gray-400 sm:text-xs">
                                        {{ game.file ? 'Single File' : 'Collection' }}
                                    </p>
                                </div>

                                <!-- PvP Info -->
                                <div class="mb-4 rounded-md border-2 border-red-500 bg-black/50 p-2">
                                    <div class="flex items-center">
                                        <svg
                                            class="pixel-outline-icon mr-2 h-4 w-4 text-red-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                            ></path>
                                        </svg>
                                        <p class="pixel-outline text-sm font-medium text-red-700">Player vs Player Battle</p>
                                    </div>
                                </div>

                                <!-- Join Button -->
                                <button
                                    @click="joinGame(game.id)"
                                    :disabled="joiningGameId === game.id"
                                    class="pixel-outline w-full rounded-md bg-green-500 px-4 py-3 text-base font-medium text-white transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800 sm:py-2 sm:text-sm"
                                >
                                    <span v-if="joiningGameId === game.id" class="flex items-center justify-center">
                                        <svg class="pixel-outline-icon mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        Joining...
                                    </span>
                                    <span v-else>Join Game</span>
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <svg class="pixel-outline-icon mx-auto h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                ></path>
                            </svg>
                            <h3 class="pixel-outline mt-2 text-sm font-medium text-gray-100">No games available</h3>
                            <p class="pixel-outline mt-1 text-sm text-gray-400">
                                No one has created a multiplayer game yet. Be the first to start a battle!
                            </p>
                            <div class="mt-6">
                                <button
                                    @click="activeTab = 'create'"
                                    class="pixel-outline inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800"
                                >
                                    Create New Game
                                </button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="waitingGames.links && waitingGames.data.length > 0" class="mt-4 sm:mt-6">
                            <Pagination :links="waitingGames as any" />
                        </div>
                    </div>
                </div>

                <!-- Create Game Tab -->
                <div v-show="activeTab === 'create'" class="bg-container mx-2 overflow-hidden shadow-sm sm:mx-4 sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        <h3 class="pixel-outline mb-4 text-lg font-medium text-gray-100">Create a New Multiplayer Game</h3>

                        <!-- General Error Display -->
                        <div
                            v-if="form.errors.general || Object.keys(form.errors).length > 0"
                            class="pixel-outline mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="pixel-outline text-sm font-medium text-red-800 dark:text-red-200">
                                        There were errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-inside list-disc space-y-1">
                                            <li v-if="form.errors.general">{{ form.errors.general }}</li>
                                            <li v-for="(error, field) in form.errors" :key="field" v-if="field !== 'general'">
                                                <strong>{{ field }}:</strong> {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Game Mode Selection -->
                        <!-- Game Mode Selection (PvP Only) -->
                        <div class="mb-6">
                            <label class="pixel-outline mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Game Mode </label>
                            <div class="rounded-lg border-2 border-purple-500 bg-black/50 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="pixel-outline font-medium text-white">PvP (Player vs Player)</h3>
                                        <p class="pixel-outline text-sm text-gray-300">Battle against another player</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PvP Win Condition Toggle -->
                        <div class="mb-6">
                            <label class="pixel-outline mb-2 block text-sm font-medium text-gray-300">PvP Win Condition</label>
                            <div class="flex space-x-4">
                                <button
                                    type="button"
                                    @click="form.pvp_mode = 'accuracy'"
                                    :class="form.pvp_mode === 'accuracy' ? 'pixel-outline bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    class="rounded-md px-4 py-2 text-sm font-medium"
                                >
                                    Most Accurate Wins
                                </button>
                                <button
                                    type="button"
                                    @click="form.pvp_mode = 'hp'"
                                    :class="form.pvp_mode === 'hp' ? 'pixel-outline bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    class="rounded-md px-4 py-2 text-sm font-medium"
                                >
                                    Most HP Wins
                                </button>
                            </div>
                            <p class="pixel-outline mt-2 text-xs text-gray-400">Choose how the winner is determined in PvP mode.</p>
                        </div>

                        <!-- Source Selection -->
                        <div class="mb-6">
                            <span class="pixel-outline block text-sm font-medium text-gray-300">Select Source</span>
                            <div class="mt-2 flex space-x-4">
                                <button
                                    @click="form.source_type = 'file'"
                                    :class="[
                                        'pixel-outline flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all',
                                        form.source_type === 'file'
                                            ? 'border-2 border-purple-500 bg-black/50 text-white shadow-md transition-transform duration-500 hover:scale-105'
                                            : 'border-2 border-gray-500 bg-black/50 text-gray-300 transition-transform duration-500 hover:scale-105',
                                    ]"
                                >
                                    Single File
                                </button>
                                <button
                                    @click="form.source_type = 'collection'"
                                    :class="[
                                        'pixel-outline flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all',
                                        form.source_type === 'collection'
                                            ? 'border-2 border-purple-500 bg-black/50 text-white shadow-md transition-transform duration-500 hover:scale-105'
                                            : 'border-2 border-gray-500 bg-black/50 text-gray-300 transition-transform duration-500 hover:scale-105',
                                    ]"
                                >
                                    Collection
                                </button>
                            </div>
                        </div>

                        <!-- File/Collection Selection -->
                        <div class="mb-6">
                            <div v-if="form.source_type === 'file'" class="flex flex-col">
                                <label class="pixel-outline mb-2 text-sm font-medium text-gray-300" for="file_id"> Select File </label>
                                <select
                                    v-model="form.file_id"
                                    id="file_id"
                                    class="pixel-outline rounded-md border border-gray-300 bg-black/50 px-3 py-2 text-sm shadow-sm"
                                >
                                    <option value="">-- Select a file --</option>
                                    <option v-for="file in props.files" :key="file.id" :value="file.id" class="bg-black text-white">
                                        {{ file.title || file.name }}
                                    </option>
                                </select>
                            </div>
                            <div v-else-if="form.source_type === 'collection'" class="flex flex-col">
                                <label class="pixel-outline mb-2 text-sm font-medium text-gray-300" for="collection_id"> Select Collection </label>
                                <select
                                    v-model="form.collection_id"
                                    id="collection_id"
                                    class="pixel-outline rounded-md border border-gray-300 bg-black/50 px-3 py-2 text-sm shadow-sm"
                                >
                                    <option value="">-- Select a collection --</option>
                                    <option
                                        v-for="collection in props.collections"
                                        :key="collection.id"
                                        :value="collection.id"
                                        class="bg-black text-white"
                                    >
                                        {{ collection.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Private Game Option -->
                        <div class="mb-6">
                            <label class="pixel-outline mb-2 block text-sm font-medium text-gray-300">Game Visibility</label>
                            <div class="flex space-x-4">
                                <button
                                    type="button"
                                    @click="form.is_private = false"
                                    :class="form.is_private === false ? 'pixel-outline bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    class="rounded-md px-4 py-2 text-sm font-medium"
                                >
                                    Public Game
                                </button>
                                <button
                                    type="button"
                                    @click="form.is_private = true"
                                    :class="form.is_private === true ? 'pixel-outline bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                                    class="rounded-md px-4 py-2 text-sm font-medium"
                                >
                                    Private Game
                                </button>
                            </div>
                            <p class="pixel-outline mt-2 text-xs text-gray-400">
                                Public games appear in the lobby for others to join. Private games can only be joined via code.
                            </p>
                        </div>

                        <!-- PvP Game Info -->
                        <div class="mb-6 rounded-lg border border-2 border-red-800 bg-red-900/20 p-4">
                            <h3 class="pixel-outline mb-2 text-lg font-medium text-red-100">How PvP (Versus) Battles Work</h3>
                            <ul class="pixel-outline space-y-1 text-sm text-red-300">
                                <li>• You and another player take turns answering questions</li>
                                <li>• Correct answers deal damage to your opponent</li>
                                <li>• Wrong answers cause damage to yourself</li>
                                <li>• Be the last player standing to win!</li>
                            </ul>
                        </div>

                        <!-- Private Game Info (when private is selected) -->
                        <div v-if="form.is_private" class="mb-6 rounded-lg border border-2 border-purple-800 bg-purple-900/20 p-4">
                            <h3 class="pixel-outline mb-2 text-lg font-medium text-purple-100">Private Game Features</h3>
                            <ul class="pixel-outline space-y-1 text-sm text-purple-300">
                                <li>• Your game won't appear in the public lobby</li>
                                <li>• Players can only join using the game code</li>
                                <li>• You'll receive a unique game code to share</li>
                                <li>• Perfect for challenging specific friends!</li>
                            </ul>
                        </div>

                        <!-- Create Button -->
                        <div>
                            <button
                                @click="submit"
                                :disabled="!canSubmit || form.processing"
                                class="pixel-outline inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                            >
                                <span v-if="form.processing" class="flex items-center justify-center">
                                    <svg class="pixel-outline-icon mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Creating...
                                </span>
                                <span v-else>Create Game</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- My Games Tab -->
                <div v-show="activeTab === 'mygames'" class="bg-container mx-2 overflow-hidden shadow-sm sm:mx-4 sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        <h3 class="pixel-outline mb-3 text-base font-medium text-gray-100 sm:mb-4 sm:text-lg">My Multiplayer Games</h3>

                        <!-- Games Grid -->
                        <div v-if="myGames.data.length > 0" class="grid grid-cols-1 gap-3 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="game in myGames.data"
                                :key="game.id"
                                class="overflow-hidden border-2 border-green-500 bg-black/50 shadow-sm transition-shadow hover:shadow-lg sm:rounded-lg"
                            >
                                <div class="p-4">
                                    <!-- Game Header -->
                                    <div class="mb-4 flex items-start justify-between">
                                        <div class="flex-1">
                                            <span
                                                :class="getStatusBadgeClass(game.status, game)"
                                                class="pixel-outline inline-flex items-center justify-end rounded-full px-2 py-1 text-xs font-medium"
                                            >
                                                {{ getStatusLabel(game.status, game) }}
                                            </span>
                                            <div class="mb-2 flex items-center justify-center gap-2">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-900/20">
                                                    <svg
                                                        class="pixel-outline-icon h-4 w-4 text-red-500"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                                        ></path>
                                                    </svg>
                                                </div>
                                                <h3 class="pixel-outline text-lg font-medium text-red-700">PvP Battle</h3>
                                            </div>
                                            <p class="pixel-outline mb-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                                {{ getSourceName(game) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Players -->
                                    <div class="mb-4">
                                        <div class="-mx-5 flex items-center justify-between border-2 border-green-500 bg-black/50 p-4 text-sm">
                                            <div class="flex items-center space-x-2">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="pixel-outline flex h-6 w-6 items-center justify-center rounded-full border-2 border-green-500 bg-black/50 text-green-300"
                                                    >
                                                        {{ game.player_one.first_name.charAt(0) }}
                                                    </div>
                                                </div>
                                                <span class="pixel-outline text-green-300"
                                                    >{{ game.player_one.first_name }} {{ game.player_one.last_name }}</span
                                                >
                                                <span v-if="game.status !== 'waiting'" class="text-red-500">{{ game.player_one_hp }}❤️</span>
                                            </div>
                                            <span class="mx-3 text-gray-400">&</span>
                                            <div class="flex items-center space-x-2">
                                                <span v-if="game.status !== 'waiting'" class="text-red-500">{{ game.player_two_hp }}❤️</span>
                                                <span v-if="game.player_two" class="pixel-outline text-green-300"
                                                    >{{ game.player_two.first_name }} {{ game.player_two.last_name }}</span
                                                >
                                                <span v-else class="pixel-outline text-gray-500 italic">Waiting for player</span>
                                                <div v-if="game.player_two" class="flex-shrink-0">
                                                    <div
                                                        class="pixel-outline flex h-6 w-6 items-center justify-center rounded-full border-2 border-green-500 bg-black/50 text-xs font-medium text-green-300"
                                                    >
                                                        {{ game.player_two.first_name.charAt(0) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Game Stats -->
                                    <div
                                        v-if="game.status !== 'waiting'"
                                        class="mb-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <div class="flex items-center space-x-4">
                                            <span>Score: {{ game.player_one_score }} - {{ game.player_two_score }}</span>
                                        </div>
                                        <span class="text-xs">{{ formatTimeAgo(game.created_at) }}</span>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="flex justify-center">
                                        <Link
                                            v-if="game.status === 'waiting' || game.status === 'active'"
                                            :href="route('multiplayer-games.show', game.id)"
                                            class="pixel-outline inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                        >
                                            {{ game.status === 'waiting' ? 'Enter Waiting Room' : 'Continue Game' }}
                                        </Link>
                                        <Link
                                            v-else
                                            :href="route('multiplayer-games.show', game.id)"
                                            class="pixel-outline inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                        >
                                            View Results
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State for My Games -->
                        <div v-else class="py-12 text-center">
                            <svg class="pixel-outline-icon mx-auto h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                ></path>
                            </svg>
                            <h3 class="pixel-outline mt-2 text-sm font-medium text-gray-100">No games yet</h3>
                            <p class="pixel-outline mt-1 text-sm text-gray-500">You haven't created or joined any multiplayer games yet.</p>
                            <div class="mt-6">
                                <button
                                    @click="activeTab = 'create'"
                                    class="pixel-outline inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800"
                                >
                                    Create Your First Game
                                </button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="myGames.links && myGames.data.length > 0" class="mt-4 sm:mt-6">
                            <Pagination :links="myGames as any" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { getInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Player {
    id: number;
    first_name: string;
    last_name: string;
}

interface File {
    id: number;
    name: string;
    title?: string;
    quizzes_count?: number;
}

interface Collection {
    id: number;
    name: string;
    file_count: number;
    total_questions: number;
}

interface Game {
    id: number;
    player_one: Player;
    player_two?: Player;
    file?: File;
    collection?: Collection;
    game_mode: 'pvp';
    created_at: string;
    status: string;
    player_one_hp: number;
    player_two_hp: number;
    player_one_score: number;
    player_two_score: number;
    winner_id?: number;
    pvp_mode?: string;
}

interface PaginatedGames {
    data: Game[];
    links?: any[];
}

const props = defineProps<{
    files: File[];
    collections: Collection[];
    waitingGames: PaginatedGames;
    myGames: PaginatedGames;
    file_id?: number;
    collection_id?: number;
    game_code?: string;
}>();

// Get current user
const page = usePage();
const currentUser = computed(() => page.props.auth.user);

// Tab state
const activeTab = ref<'lobby' | 'create' | 'mygames'>('lobby');

// Game creation form
const form = useForm({
    source_type: 'file' as 'file' | 'collection',
    file_id: null as number | null,
    collection_id: null as number | null,
    game_mode: 'pvp',
    pvp_mode: 'accuracy', // 'accuracy' or 'hp'
    is_private: false as boolean,
});

// Game joining state
const joiningGameId = ref<number | null>(null);

// Initialize form with URL parameters
if (props.file_id) {
    activeTab.value = 'create';
    form.source_type = 'file';
    form.file_id = props.file_id;
} else if (props.collection_id) {
    activeTab.value = 'create';
    form.source_type = 'collection';
    form.collection_id = props.collection_id;
}

// Form validation
const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id;
    const hasPvpMode = !!form.pvp_mode;
    return hasSource && hasPvpMode;
});

// Reset file/collection when source type changes
watch(
    () => form.source_type,
    () => {
        form.file_id = null;
        form.collection_id = null;
    },
);

// Form submission
const submit = () => {
    form.post(route('multiplayer-games.store'), {
        onSuccess: () => {
            // Switch to lobby tab after creating game
            activeTab.value = 'lobby';
        },
    });
};

// Join game functionality
const joinGame = async (gameId: number) => {
    joiningGameId.value = gameId;

    try {
        await router.post(route('multiplayer-games.join', gameId));
    } catch (error) {
        console.error('Failed to join game:', error);
    } finally {
        joiningGameId.value = null;
    }
};

// Join by code functionality
const showJoinByCode = ref(false);
const gameCodeForm = useForm({
    game_code: '',
});

// Initialize game code form if game_code is provided via URL
if (props.game_code) {
    showJoinByCode.value = true;
    gameCodeForm.game_code = props.game_code;
}

const joinByCode = () => {
    gameCodeForm.post(route('multiplayer-games.join-by-code'), {
        onSuccess: () => {
            showJoinByCode.value = false;
            gameCodeForm.reset();
        },
    });
};

// Utility functions
const formatTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours}h ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays}d ago`;
};

// Additional utility functions for My Games tab
const getDetailedGameResult = (game: Game) => {
    const userId = (currentUser.value as any)?.id;
    
    if (game.status === 'waiting') {
        return { label: 'Waiting for Player', class: 'bg-yellow-500 text-white border-2 border-yellow-600 shadow-lg' };
    }
    
    if (game.status === 'active') {
        return { label: 'Game in Progress', class: 'bg-blue-600 text-white border-2 border-blue-700 shadow-lg' };
    }
    
    if (game.status === 'forfeited') {
        // Check if current user forfeited or opponent forfeited
        if (game.winner_id === userId) {
            return { label: 'Victory (Opponent Forfeited)', class: 'bg-green-600 text-white border-2 border-green-700 shadow-lg' };
        } else {
            return { label: 'Defeat (You Forfeited)', class: 'bg-red-600 text-white border-2 border-red-700 shadow-lg' };
        }
    }
    
    if (game.status === 'finished') {
        // Determine if current user won or lost
        if (game.winner_id === userId) {
            return { label: 'Victory', class: 'bg-green-600 text-white border-2 border-green-700 shadow-lg' };
        } else if (game.winner_id && game.winner_id !== userId) {
            return { label: 'Defeat', class: 'bg-red-600 text-white border-2 border-red-700 shadow-lg' };
        } else {
            // No winner determined (tie or incomplete)
            return { label: 'Draw', class: 'bg-purple-600 text-white border-2 border-purple-700 shadow-lg' };
        }
    }
    
    return { label: 'Unknown Status', class: 'bg-gray-600 text-white border-2 border-gray-700 shadow-lg' };
};

const getStatusBadgeClass = (status: string, game: Game) => {
    return getDetailedGameResult(game).class;
};

const getStatusLabel = (status: string, game: Game) => {
    return getDetailedGameResult(game).label;
};

const getSourceName = (game: any) => {
    if (game.file) {
        return game.file.title || game.file.name;
    } else if (game.collection) {
        return game.collection.name;
    }
    return 'Unknown Source';
};

// Real-time lobby updates via websockets
let echo: any;

onMounted(() => {
    // Listen for lobby updates
    if (window.Echo) {
        echo = window.Echo.channel('multiplayer-lobby').listen('MultiplayerGameLobbyUpdate', () => {
            // Refresh the page to get updated games
            router.reload({ only: ['waitingGames'] });
        });
    }
});

onUnmounted(() => {
    if (echo) {
        echo.stopListening('MultiplayerGameLobbyUpdate');
    }
});
</script>
