import React, {Fragment} from 'react';
import ReactInputMask from 'react-input-mask';

class EncryptedTextInput extends React.PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            encryptedValue: "",
            unencryptedValue: '',
            isEncrypted: true,
            hasBeenDecrypted: false
        }

        this.handleChange = this.handleChange.bind(this);
        this.decryptValue = this.decryptValue.bind(this);
    }

    encryptAndSave = () => {
        if (this.props.onChange) {
            if (!this.state.unencryptedValue) {
                return this.props.onChange(this.state.unencryptedValue);
            }
            const formData = new FormData();
            formData.append('value', this.state.unencryptedValue);
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
    };

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (prevProps.encryptedValue !== this.props.encryptedValue) {
            this.setState({isEncrypted: true, unencryptedValue: ''})
        }
    }

    handleChange(e) {
        let value = e.target.value;
        if (this.props.replaceFunction) {
            value = this.props.replaceFunction(value);
        }
        this.setState({unencryptedValue: value});
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
        if (!this.props.encryptedValue) {
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
        formData.append('encryptedData', this.props.encryptedValue);
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
                    return json.decryptedData || "";
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

        if (this.state.isEncrypted) {
            return (
                <button type="button"
                        onClick={this.decryptValue}
                        className='form-control input-sm'
                >
                    <i className="fal fa-pencil-alt greenPencil"/>
                </button>
            );
        }
        return <Fragment>
            <ReactInputMask
                value={this.state.unencryptedValue}
                onChange={this.handleChange}
                mask={this.props.mask}
                alwaysShowMask={true}
                className='form-control '
                style={{display: 'inline', width: "80%"}}
            />
            <button type="button" onClick={this.encryptAndSave}>
                <i className="fal fa-lock"/>
            </button>
        </Fragment>
    }
}

export default EncryptedTextInput;
