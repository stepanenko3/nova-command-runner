<template>
    <div class="nova-command-runner">
        <div class="text-lg font-semibold mb-6">
            {{ heading }}
        </div>

        <div
            class="flex flex-col md:flex-row mb-3 md:space-x-3 space-y-2 md:space-y-0"
            v-if="!Array.isArray(customCommands)"
        >
            <select
                v-model="customCommandType"
                class="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-800 h-10 px-3 rounded-lg"
            >
                <option
                    v-for="(name, value) in customCommands"
                    :key="value"
                    :value="value"
                >
                    {{ name }}
                </option>
            </select>

            <input
                type="text"
                v-model="customCommand"
                class="appearance-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-800 h-10 px-3 rounded-lg flex-grow"
            />

            <Button @click="runCustomCommand">
                {{ __("Run") }}
            </Button>
        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow mb-3 grid md:grid-cols-12 gap-6 p-6"
        >
            <div
                v-for="(group, index) in groups"
                class="md:col-span-3 grid gap-3 content-start"
                :key="index"
            >
                <div class="text-sm font-semibold">
                    {{ group ? group : __("Unnamed group") }}
                </div>
                <template v-for="(command, index) in commands">
                    <Button
                        v-if="command.group == group"
                        :loading="!modalOpen && running"
                        :theme="command.type"
                        @click="openModal(command)"
                    >
                        {{ command.label }}
                    </Button>
                </template>
            </div>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center justify-between mt-6 mb-6"
        >
            <div class="font-bold">{{ __("History") }}</div>

            <div
                class="md:ml-2 inline-flex items-center shadow rounded-lg bg-white dark:bg-gray-800 px-2 h-8"
            >
                <ToolbarButton
                    @click.prevent="getData"
                    :loading="loading"
                    type="refresh"
                />

                <ToolbarButton
                    @click.prevent="playing = !playing"
                    :class="{
                        'text-green-500': playing,
                        'text-gray-500': !playing,
                    }"
                    type="clock"
                    class="w-8 h-8"
                />

                <ToolbarButton
                    @click.prevent="clearHistory"
                    type="trash"
                    class="text-red-500"
                />
            </div>
        </div>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow mb-3 p-2 overflow-y-hidden overflow-x-auto"
        >
            <table
                v-if="history && Object.keys(history).length > 0"
                class="w-full"
            >
                <thead>
                    <tr>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Command") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Type") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Run By") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Status") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Result") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Duration") }}
                        </th>
                        <th
                            class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2"
                        >
                            {{ __("Happened") }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(value, index) in history" :key="index">
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            {{ value.run }}
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            {{ value.type }}
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            {{ value.run_by }}
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                            :class="{
                                '!text-green-500': value.status === 'success',
                                '!text-yellow-500': value.status === 'pending',
                                '!text-red-500': value.status === 'error',
                            }"
                        >
                            {{ value.status }}
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            <pre v-html="value.result"></pre>
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            {{ value.duration ? value.duration + " sec." : "" }}
                        </td>
                        <td
                            class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900"
                        >
                            {{ value.time }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div
                v-else
                class="flex flex-col justify-center items-center px-6 py-8"
            >
                <Icon
                    type="search"
                    class="mb-3 text-gray-300 dark:text-gray-500"
                    width="50"
                    height="50"
                ></Icon>

                <h3 class="text-base font-normal mt-3">
                    {{ __("No Logs.") }}
                </h3>
            </div>
        </div>

        <div
            class="fixed inset-0 z-[60] bg-gray-500/75 dark:bg-gray-900/75 flex items-center justify-center p-3"
            v-if="modalOpen"
        >
            <div
                class="absolute inset-0"
                @click.prevent="() => (modalOpen = false)"
            ></div>
            <div
                class="relative p-6 bg-white dark:bg-gray-800 rounded-lg shadow min-w-80 md:w-1/2 max-w-full max-h-full overflow-y-auto"
            >
                <div
                    class="absolute top-0 right-0 w-5 cursor-pointer hover:opacity-75 text-lg"
                    @click.prevent="() => (modalOpen = false)"
                >
                    &#x2715;
                </div>

                <div class="text-lg font-bold mb-5">
                    {{ runningCommand.label }}
                </div>

                <div class="grid gap-6">
                    <p v-if="runningCommand.help">
                        {{ runningCommand.help }}
                    </p>

                    <div
                        v-for="(variable, index) in runningCommand.variables"
                        :key="index"
                    >
                        <label
                            class="mb-2 block leading-tight w-full text-sm font-semibold"
                        >
                            {{ variable.label }}
                        </label>

                        <select
                            class="appearance-none bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-900 h-10 px-3 rounded-lg"
                            v-if="variable.field === 'select'"
                            v-model="variable.value"
                        >
                            <option v-for="option in variable.options">
                                {{ option }}
                            </option>
                        </select>

                        <input
                            v-else
                            :type="variable.field"
                            class="w-full appearance-none bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-900 h-10 px-3 rounded-lg"
                            v-model="variable.value"
                            :placeholder="variable.placeholder"
                        />
                    </div>

                    <div v-if="runningCommand.flags.length">
                        <label
                            v-for="(flag, index) in runningCommand.flags"
                            :key="'flag-' + index"
                            class="flex items-center cursor-pointer"
                        >
                            <input
                                type="checkbox"
                                v-model="runningCommand.flags[index].selected"
                                :true-value="1"
                                :false-value="0"
                                class="opacity-0 absolute pointer-events-none"
                            />
                            <span
                                class="h-6 w-6 block mr-2 flex-shrink-0 rounded-lg bg-white dark:bg-gray-900 text-transparent border border-gray-300 dark:border-gray-900 flex items-center justify-center text-center"
                                :class="{
                                    '!text-gray-900':
                                        runningCommand.flags[index].selected,
                                }"
                            >
                                &#x2713;
                            </span>
                            <span>{{ flag.label }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center space-x-3 mt-5">
                    <Button theme="gray" @click.prevent="closeModal">
                        {{ __("Cancel") }}
                    </Button>

                    <Button
                        :loading="running"
                        @click="runCommand()"
                        :theme="runningCommand.type"
                    >
                        {{ runningCommand.label }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Button from "../components/Button.vue";
import ToolbarButton from "../components/ToolbarButton.vue";

const loading = ref(false);
const playing = ref(false);
const running = ref(false);
const modalOpen = ref(false);
const heading = ref("");
const interval = ref();
const history = ref();
const customCommands = ref();
const groups = ref();
const commands = ref();
const help = ref();
const pollingTime = ref();
const customCommand = ref();
const customCommandType = ref();
const runningCommand = ref();

onMounted(async () => {
    await getData();

    setupInterval();
});

async function getData() {
    if (loading.value) return;

    loading.value = true;

    const response = await fetch(
        "/nova-vendor/stepanenko3/nova-command-runner"
    );

    if (response.ok) {
        const data = await response.clone().json();

        heading.value = data.heading;
        history.value = data.history;
        commands.value = data.commands;
        help.value = data.help;
        pollingTime.value = data.polling_time;

        groups.value = [];

        data.commands.forEach((command) => {
            let group = command.group;

            if (groups.value.indexOf(group) < 0) {
                groups.value.push(group);
            }
        });

        customCommands.value = data.custom_commands;

        if (
            customCommands.value &&
            Object.keys(customCommands.value).indexOf(
                customCommandType.value
            ) === -1
        ) {
            customCommandType.value = Object.keys(customCommands.value)[0];
        }
    }

    loading.value = false;
}

function setupInterval() {
    interval.value = setInterval(() => {
        if (
            !loading.value &&
            (playing.value ||
                history.value.filter((n) => n.status === "pending").length > 0)
        ) {
            getData();
        }
    }, pollingTime.value);
}

function clearHistory() {
    openModal({
        label: __("Clear Command Run History"),
        type: "primary",
        help: __("Are you sure you want to clear the command run history?"),
        command_type: "artisan",
        command: "cache:forget nova-command-runner-history",
        variables: [],
        flags: [],
    });
}

function openModal(command) {
    runningCommand.value = command;
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    runningCommand.value = {};
}

function runCommand() {
    let readyToSubmit = true;

    if (runningCommand.value.variables) {
        Object.keys(runningCommand.value.variables).forEach((variable) => {
            if (
                runningCommand.value.variables[variable].value == null ||
                runningCommand.value.variables[variable].value.length === 0
            ) {
                readyToSubmit = false;

                Nova.error(
                    runningCommand.value.variables[variable].label +
                        " is required"
                );
            }
        });
    }

    if (!readyToSubmit) {
        return;
    }

    running.value = true;

    fetch("/nova-vendor/stepanenko3/nova-command-runner/run", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.head.querySelector(
                'meta[name="csrf-token"]'
            ).content,
            "X-Requested-With": "XMLHttpRequest",
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            command: runningCommand.value,
        }),
    })
        .then(async (response) => {
            if (response.ok) {
                const data = await response.json();

                if (
                    data.status &&
                    (data.status === "success" || data.status === "pending")
                ) {
                    Nova.success(data.result);
                } else {
                    Nova.error(data.result);
                }

                running.value = false;
                history.value = data.history;
                closeModal();
            } else {
                Nova.error(
                    `${response.status}: ${
                        response.statusText || "Something went wrong"
                    }`
                );

                running.value = false;
                closeModal();
            }
        })
        .catch((error) => {
            running.value = false;
        });
}

function runCustomCommand() {
    if (!customCommand.value) {
        Nova.error("Please enter a command");
        return;
    }

    openModal({
        label: __("Custom Command"),
        type: "primary",
        help: __("Are you sure you want to run this command?"),
        command_type: customCommandType.value,
        command: customCommand.value,
        variables: [],
        flags: [],
    });
}
</script>
