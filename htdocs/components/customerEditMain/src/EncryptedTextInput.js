import React from 'react';
import ReactInputMask from 'react-input-mask';

class EncryptedTextInput extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            encryptedValue: props.encryptedValue,
            unencryptedValue: '',
            onChange: props.onChange,
            isEncrypted: !!props.encryptedValue
        }

        this.handleChange = this.handleChange.bind(this);
        this.decryptValue = this.decryptValue.bind(this);
    }

    handleChange(e) {
        this.setState({unencryptedValue: e.target.value.replace(/[^0-9]+/g, "")});
        if (this.props.onChange) {
            if (!e.target.value) {
                return this.props.onChange(e.target.value);
            }
            const formData = new FormData();
            formData.append('value', e.target.value);
            fetch('?action=encrypt', {method: 'POST', body: formData})
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                })
                .then(response => {
                    this.setState({encryptedValue: response.data});
                    this.props.onChange(response.data);
                })
        }
    }

    storePassPhrase(passPhrase) {
        document.storedPassPhrase = passPhrase;
        setTimeout(() => {
            document.storedPassPhrase = null;
        }, 30000);
    }

    decryptValue() {
        if (!this.state.isEncrypted) {
            return;
        }
        if (!this.state.encryptedValue) {
            return this.setState({unencryptedValue: '', isEncrypted: false});
        }

        let passPhrase = document.storedPassPhrase;
        if (!passPhrase) {
            passPhrase = prompt('Please provide secure passphrase');
            this.storePassPhrase(passPhrase);
        }
        if (!passPhrase) {
            return;
        }

        const formData = new FormData();

        formData.append('passphrase', passPhrase);
        formData.append('encryptedData', this.state.encryptedValue);
        fetch('?action=decrypt', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (response.ok) {
                    return response.json()
                }
                throw 'Unable to decrypt';
            })
            .then(json => {
                if (json) {
                    return json.decryptedData;
                } else {
                    return null;
                }
            })
            .then(decryptedValue => {
                this.setState({unencryptedValue: decryptedValue, isEncrypted: false});
            })
            .catch(error => {
                alert('Unable to decrypt, please check passphrase and try again');
                return null;
            })
    }

    render() {


        if (this.state.isEncrypted && this.state.encryptedValue) {
            return (
                <button type="button" onClick={this.decryptValue}
                        className='form-control'
                >
                    <i className="fa fa-pencil-alt greenPencil"/>
                </button>
            );
        }

        // not encrypted ...we need to show the input

        return <ReactInputMask
            value={this.state.unencryptedValue}
            onChange={this.handleChange}
            mask={this.props.mask}
            alwaysShowMask={true}
            className='form-control'
        />
    }
}

export default EncryptedTextInput;
