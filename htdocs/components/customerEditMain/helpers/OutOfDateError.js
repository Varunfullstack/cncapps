export class OutOfDateError extends Error {
    lastUpdate;

    constructor(message, lastUpdate) {
        super(message);
        this.lastUpdate = lastUpdate;
    }

}