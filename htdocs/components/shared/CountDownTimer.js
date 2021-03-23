import React from 'react';

class CountDownTimer extends React.Component {
    el = React.createElement;
    timer = 0;

    constructor(props) {
        super(props);

        this.state = {
            time: {},
            seconds: props.seconds,
        };
        this.timer = 0;
    }

    componentDidMount() {
        this.startTimer();
    }

    // count down
    countDown = () => {
        // Remove one second, set state so a re-render happens.
        let seconds = this.state.seconds - 1;
        this.setState({
            time: this.secondsToTime(seconds),
            seconds: seconds,
        });

        // Check if we're at zero.
        if (seconds == 0) {
            clearInterval(this.timer);
        }
    };
    startTimer = () => {
        if (this.timer == 0 && this.state.seconds > 0) {
            this.timer = setInterval(this.countDown, 1000);
        }
    };

    secondsToTime(secs) {
        let hours = Math.floor(secs / (60 * 60));

        let divisor_for_minutes = secs % (60 * 60);
        let minutes = Math.floor(divisor_for_minutes / 60)  + hours * 60;

        let divisor_for_seconds = divisor_for_minutes % 60;
        let seconds = Math.ceil(divisor_for_seconds);

        return {
            h: hours,
            m: minutes,
            s: seconds,
        };
    }

    getRemainTime = () => {
        const {el} = this;
        const {time} = this.state;
        return el(
            "div",
            {style: {display: "flex", flexDirection: "row", justifyContent: "center", alignItems: "center"}},
            el("i", {
                className: "fal fa-stopwatch fa-2x m-5 pointer icon",
            }),
            !this.props.hideMinutesTitle ? el("label", null, "m:") : null,
            el("label", {style: {fontWeight: "bold", fontSize: 12}}, time.m),
            !this.props.hideSeconds ? el("label", null, " s:") : null,
            !this.props.hideSeconds ? el("label", null, time.s) : null,
        );
    };

    render() {
        return this.getRemainTime();
    }
}

export default CountDownTimer;
