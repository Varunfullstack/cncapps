import React from 'react';

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
        const {className} = this.props;

        return (<select className={className}
                        value={this.state.selectedOption}
                        onChange={this.handleChange}
        >
            {this.getOptions(this.state.options)}
        </select>)
    }

    getOptions(options) {
        if (!options) return;

        return [{value: '', label: 'Select One Option'}, ...options].map((x, idx) =>
            <option value={x.value}
                    key={x.value}
            >{x.label}</option>
        )
    }
}

export default Select;
