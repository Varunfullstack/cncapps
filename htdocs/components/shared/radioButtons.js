import React from 'react';
export const RadioButtonsType={vertical:"vertical",horizontal:"horizontal"};
export default class RadioButtons extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            selectedOption: props?.value ? props.value : null
        }
    }

    handleOnChange = (id) => {
        this.setState({selectedOption: id});
        if (this.props.onChange)
            this.props.onChange(id);
    }

    render() {
        const {el, handleOnChange} = this;
        let {items,disabled,mode,center}=this.props;
        const {selectedOption} = this.state;
        if(!mode){
            mode=RadioButtonsType.vertical;
        }

        if (items && items.length > 0) {
            return el('div', {key: 'divRadioList', className: 'radio-list'+(mode===RadioButtonsType.horizontal?" horizontal":"")+(center?" content-center ":"")}, [
                items.map(item => el('div', {key: 'div' + item.id, className: 'radio'}, [
                    el('label', {key: 'lb' + item.id}, [
                        el('input', {
                            key: 'ip' + item.id,
                            type: "radio",
                            disabled: disabled ? 'disabled' : null,
                            value: item.id,
                            checked: selectedOption === item.id,
                            onChange: () => handleOnChange(item.id)
                        }),
                        el('span', {key: 'span' + item.id}, item.name)
                    ])
                ]))
            ])
        } else return null;
    }
}