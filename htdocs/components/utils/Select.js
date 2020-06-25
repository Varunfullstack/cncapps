class Select extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            selectedOption: props.selectedOption,
            options: props.options,
            onChange: props.onChange
        }

        this.handleChange = this.handleChange.bind(this);
    }

    handleChange(e) {
        this.setState({selectedOption: e.target.value});
        if (this.props.onChange) {
            this.props.onChange(e.target.value);
        }
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (prevProps.selectedOption !== this.props.selectedOption || prevProps.options !== this.props.options) {
            this.setState({options: this.props.options, selectedOption: this.props.selectedOption});
        }
    }

    render() {
        return this.el("select", {
            value: this.state.selectedOption,
            onChange: this.handleChange
        }, this.getOptions(this.state.options));
    }

    getOptions(options) {
        if (!options) return [];
        return options.map(x =>
            this.el('option', {
                value: x.value,
                key: x.value
            }, x.label)
        )
    }
}

export default Select;
