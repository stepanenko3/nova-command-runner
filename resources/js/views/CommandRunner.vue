<template>
    <div>
        <Modal
            :show="modalOpen"
            tabindex="-1"
            data-testid="command-runner-modal"
            role="dialog"
        >
            <div class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <ModalHeader v-text="runningCommand.label" />

                <div class="p-6 grid gap-6">
                    <p v-if="runningCommand.help">{{ runningCommand.help }}</p>
                    
                    <div v-for="variable,index in runningCommand.variables">
                        <label class="inline-block text-80 mb-2 leading-tight w-full capitalize">{{ variable.label }}</label>

                        <SelectControl
                            size="lg"
                            v-if="variable.field === 'select'"
                            :dusk="variable.label"
                            :options="getOptions(variable.options)"
                            :selected="getOptions(variable.options)[0]['value'] || ''"
                            @change="variable.value = $event"
                        />
                        
                        <input
                            v-else
                            :type="variable.field"
                            class="w-full form-control form-input form-input-bordered w-full"
                            v-model="variable.value"
                            :placeholder="variable.placeholder"
                        />
                    </div>

                    <div v-if="runningCommand.flags.length">
                        <CheckboxWithLabel
                            v-for="flag,index in runningCommand.flags"
                            :key="'flag-'+index"
                            v-model="runningCommand.flags[index].selected"
                        >
                            {{ flag.label }}
                        </CheckboxWithLabel>
                    </div>
                </div>

                <ModalFooter>
                    <div class="flex items-center ml-auto">
                        <CancelButton
                            component="button"
                            type="button"
                            dusk="cancel-action-button"
                            class="ml-auto mr-3"
                            @click.prevent="closeModal"
                        />

                        <LoadingButton
                            type="submit"
                            ref="runButton"
                            dusk="confirm-run-button"
                            :disabled="running"
                            :loading="running"
                            @click="runCommand()"
                            :component="!runningCommand.type || ['primary', 'danger'].indexOf(runningCommand.type) !== -1 ? (runningCommand.type === 'danger' ? 'DangerButton' : 'DefaultButton') : BasicButton"
                            :class="!runningCommand.type || ['primary', 'danger'].indexOf(runningCommand.type) !== -1 ? '' : ('btn-' + runningCommand.type)"
                        >
                            {{ runningCommand.label }}
                        </LoadingButton>
                    </div>
                </ModalFooter>
            </div>
        </Modal>

        <Heading class="mb-6">{{ heading }}</Heading>

        <Template v-if="help">
            <Card class="p-3">{{ help }}</Card>
        </Template>

        <div class="flex flex-col md:flex-row mb-3" v-if="!Array.isArray(customCommands)">
            <SelectControl
                class="md:w-1/5 mb-2 md:mb-0"
                :options="getOptions(customCommands, false)"
                :selected="getOptions(customCommands, false)[0]['value'] ?? ''"
                dusk="command-runner-type"
                size="lg"
                @change="customCommand.command_type = $event"
            />

            <div class="w-full md:w-3/5 md:px-3 mb-2 md:mb-0">
                <input type="text" v-model="customCommand.command" placeholder="Enter a Command..." class="w-full form-control form-input form-input-bordered">
            </div>

            <DefaultButton
                size="lg"
                @click="runCustomCommand"
                class="w-full md:w-1/3"
            >
                Run
            </DefaultButton>
        </div>

        <Card class="grid md:grid-cols-12 gap-6 p-6">
            <div v-for="group in groups" class="md:col-span-3 grid gap-2 content-start">
                <Heading level="2">
                    {{ group ? group : 'Unnamed group' }}
                </Heading>
                <template v-for="(command, index) in commands">
                    <LoadingButton
                        size="lg"
                        ref="button"
                        class="shadow relative w-full mt-2"
                        v-if="command.group == group"
                        :disabled="running"
                        @click="openModal(command)"
                        :component="!command.type || ['primary', 'danger'].indexOf(command.type) !== -1 ? (command.type === 'danger' ? 'DangerButton' : 'DefaultButton') : BasicButton"
                        :class="!command.type || ['primary', 'danger'].indexOf(command.type) !== -1 ? '' : ('btn-' + command.type)"
                    >
                        {{ command.label }}
                    </LoadingButton>
                </template>
            </div>
        </Card>

        <div class="flex flex-col md:flex-row justify-between mt-6 mb-6">
            <heading>History</heading>
            <DangerButton class="items-right md:mt-2" @click="clearHistory">
                Clear History
            </DangerButton>
        </div>

        <card class="mb-6 overflow-hidden overflow-x-auto relative">
            <table class="w-full table-default">
                <thead>
                    <tr>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Command
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Type
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Run By
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Status
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Result
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Duration
                        </th>
                        <th class="text-center px-2 whitespace-nowrap uppercase text-gray-500 text-xxs tracking-wide py-2">
                            Happened
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="value in history">
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            {{ value.run }}
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            {{ value.type }}
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            {{ value.run_by }}
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            <Badge :label="value.status" :extraClasses="'badge-'+value.status" />
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            <pre v-html="value.result"></pre>
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            {{ value.duration ? value.duration + ' sec.' : '' }}
                        </td>
                        <td class="px-2 py-2 border-t border-gray-100 dark:border-gray-700 whitespace-nowrap cursor-pointer dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-900">
                            {{ value.time }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </card>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                modalOpen: false,
                running: false,
                groups: [],
                commands: {},
                customCommand : {
                    label : 'Custom Command',
                    type : 'primary',
                    help : 'are you sure you want to run this command?',
                    command_type :'artisan',
                    command : '',
                    variables : [],
                    flags : []
                },
                history: {},
                runningCommand : {},
                help : '',
                heading : '',
                customCommands : {},
            }
        },
        created() {
            this.getData();
        },
        methods: {

            clearHistory(){
                this.openModal( {
                    label : 'Clear Command Run History',
                    type : 'primary',
                    help : 'are you sure you want to clear the command run history?',
                    command_type :'artisan',
                    command : 'cache:forget nova-command-runner-history',
                    variables : [],
                    flags : []
                } );
            },
            getOptions(options, addEmptyOption = true)
            {
                let data = [];

                if (addEmptyOption) {
                    data.push({
                        value: '',
                        label: '-',
                    });
                }

                for (let option in options) {
                    data.push({
                        value: option,
                        label: options[option],
                    });
                }

                return data;
            },
            runCustomCommand(){
                if(!this.customCommand.command){
                    Nova.error('Please enter a command');
                    return;
                }
                this.openModal( this.customCommand );
            },
            getData() {
                Nova.request().get('/nova-vendor/stepanenko3/nova-command-runner')
                    .then(response => {
                        this.groups = [];

                        response.data.commands.forEach(command => {
                            let group = command.group;
                            if (this.groups.indexOf(group) < 0) {
                                this.groups.push(group);
                            }
                        });

                        this.commands = response.data.commands;
                        this.history = response.data.history;
                        this.help = response.data.help;
                        this.heading = response.data.heading;
                        this.customCommands = response.data.custom_commands;

                        if(this.customCommands){
                            this.customCommand.command_type = Object.keys(this.customCommands)[0];
                        }
                    });
            }, 
            runCommand() {

                let readyToSubmit = true;

                if( this.runningCommand.variables ){
                    Object.keys(this.runningCommand.variables).forEach( variable => {
                        if( ! this.runningCommand.variables[variable].value ){
                            readyToSubmit = false;
                            Nova.error(this.runningCommand.variables[variable].label +' is required');
                        }
                    });
                }

                if(! readyToSubmit ){
                    return;
                }

                this.running = true;
                Nova.request().post('/nova-vendor/stepanenko3/nova-command-runner/run', { command : this.runningCommand } )
                    .then(response => {
                        if (response.data.status && response.data.status === 'success') {
                            Nova.success(response.data.result);
                        } else {
                            Nova.error(response.data.result);
                        }

                        this.running = false;
                        this.history = response.data.history;
                        this.closeModal();
                    }).catch(error => {
                    this.running = false;
                });
            },
            openModal(command) {
                this.runningCommand = command;
                this.modalOpen = true;
            },
            closeModal() {
                this.modalOpen = false;
                this.runningCommand = {};
            },
        },
    }
</script>

<style>
    .content-start {
        align-content: start;
    }

    .btn-success {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        color: #fff;
        background-color: #218838;
        border-color: #1e7e34;
    }

    .btn-success:focus, .btn-success.focus {
        box-shadow: 0 0 0 0.2rem rgba(72, 180, 97, 0.5);
    }

    .btn-success.disabled, .btn-success:disabled {
        color: #fff;
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:not(:disabled):not(.disabled):active, .btn-success:not(:disabled):not(.disabled).active,
    .show > .btn-success.dropdown-toggle {
        color: #fff;
        background-color: #1e7e34;
        border-color: #1c7430;
    }

    .btn-success:not(:disabled):not(.disabled):active:focus, .btn-success:not(:disabled):not(.disabled).active:focus,
    .show > .btn-success.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(72, 180, 97, 0.5);
    }

    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        color: #fff;
        background-color: #138496;
        border-color: #117a8b;
    }

    .btn-info:focus, .btn-info.focus {
        box-shadow: 0 0 0 0.2rem rgba(58, 176, 195, 0.5);
    }

    .btn-info.disabled, .btn-info:disabled {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:not(:disabled):not(.disabled):active, .btn-info:not(:disabled):not(.disabled).active,
    .show > .btn-info.dropdown-toggle {
        color: #fff;
        background-color: #117a8b;
        border-color: #10707f;
    }

    .btn-info:not(:disabled):not(.disabled):active:focus, .btn-info:not(:disabled):not(.disabled).active:focus,
    .show > .btn-info.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(58, 176, 195, 0.5);
    }

    .btn-warning {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-warning:hover {
        color: #212529;
        background-color: #e0a800;
        border-color: #d39e00;
    }

    .btn-warning:focus, .btn-warning.focus {
        box-shadow: 0 0 0 0.2rem rgba(222, 170, 12, 0.5);
    }

    .btn-warning.disabled, .btn-warning:disabled {
        color: #212529;
        background-color: #ffc107;
        border-color: #ffc107;
    }

    .btn-warning:not(:disabled):not(.disabled):active, .btn-warning:not(:disabled):not(.disabled).active,
    .show > .btn-warning.dropdown-toggle {
        color: #212529;
        background-color: #d39e00;
        border-color: #c69500;
    }

    .btn-warning:not(:disabled):not(.disabled):active:focus, .btn-warning:not(:disabled):not(.disabled).active:focus,
    .show > .btn-warning.dropdown-toggle:focus {
        box-shadow: 0 0 0 0.2rem rgba(222, 170, 12, 0.5);
    }

    .badge-success {
        color: #fff;
        background-color: #28a745;
    }

    .badge-error {
        color: #fff;
        background-color: #dc3545;
    }

    .badge-pending{
        color:#212529;
        background-color:#ffc107
    }
</style>
