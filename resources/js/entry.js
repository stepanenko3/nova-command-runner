Nova.booting((Vue, router, store) => {
    Nova.inertia('NovaCommandRunner', require('./views/CommandRunner').default);
});
