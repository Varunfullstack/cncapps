import React from 'react';
import Skeleton from "react-loading-skeleton";
import ReactDOM from 'react-dom';

class CustomerNotesComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerNotes: [],
            customerId: props.customerID,
            currentNote: null,
            currentNoteIdx: null,
            isAddingNote: false
        };
        document.customerNotesComponent = this;
        this.handleNoteTextChange = this.handleNoteTextChange.bind(this);
    }

    fetchCustomerNotes() {
        return fetch('/CustomerNote.php?action=getCustomerNotes&customerId=' + this.props.customerID)
            .then(response => response.json())
            .then(response => this.setState({customerNotes: response.data}));
    }

    componentDidMount() {
        this.fetchCustomerNotes()
            .then(() => {
                this.setState({
                    loaded: true,
                    currentNote: this.state.customerNotes.length ? this.state.customerNotes[0] : null,
                    currentNoteIdx: 0,
                });
            });
    }

    getIcon(iconClass) {
        return this.el('i', {className: iconClass});
    }

    createButton(className, iconClass, clickFunction, text) {
        return this.el(
            'button',
            {
                key: className,
                className,
                type: 'button',
                onClick: clickFunction
            },
            [
                this.getIcon(iconClass),
                text
            ]
        )
    }

    changeNote(nextIdx) {
        if (nextIdx >= 0 || nextIdx <= this.state.customerNotes.length - 1) {
            this.setState({
                currentNote: {...this.state.customerNotes[nextIdx]},
                currentNoteIdx: nextIdx,
                isAddingNote: false
            });
        } else {
            this.setState(
                {
                    currentNote: null,
                    currentNoteIdx: 0,
                    isAddingNote: false
                }
            )
        }

    }

    getFirstButton() {
        return this.createButton(
            'firstButton',
            'fa fa-step-backward',
            () => {
                const nextIdx = 0;
                this.changeNote(nextIdx);
            },
            ' Latest');
    }

    getPreviousButton() {
        return this.createButton(
            'previousButton',
            'fa fa-forward',
            () => {
                const nextIdx = this.state.currentNoteIdx + 1;
                this.changeNote(nextIdx);
            },
            ' Previous'
        )
    }

    getNextButton() {
        return this.createButton(
            'nextButton',
            'fa fa-backward',
            () => {
                const nextIdx = this.state.currentNoteIdx - 1;
                this.changeNote(nextIdx);
            },
            ' Next'
        )
    }

    getLastButton() {
        return this.createButton(
            'lastButton',
            'fa fa-step-forward',
            () => {
                const nextIdx = this.state.customerNotes.length - 1;
                this.changeNote(nextIdx);
            },
            ' Oldest'
        );
    }

    getDeleteButton() {
        return this.createButton(
            'deleteButton',
            'fa fa-trash',
            () => {
                if (!this.state.currentNote) {
                    return;
                }
                const currentNoteId = this.state.currentNote.id;

                fetch(`/CustomerNote.php?action=deleteNote&noteId=${currentNoteId}`)
                    .then(() => {
                        this.setState({
                            customerNotes: [...this.state.customerNotes.filter(x => x.id !== currentNoteId)]
                        });
                        const nextIdx = 0;
                        this.changeNote(nextIdx);
                    })
            },
            ' Delete'
        );
    }

    getNewButton() {
        return this.createButton(
            'newButton',
            'fa fa-plus-circle',
            () => {
                this.setState({
                    currentNote: {
                        id: -1,
                        customerId: this.props.customerID,
                        createdAt: '',
                        modifiedAt: '',
                        modifiedById: '',
                        note: '',
                        createdById: '',
                        modifiedByName: '',
                    },
                    isAddingNote: true,
                })
            },
            ' New'
        )
    }

    getSaveButton() {
        return this.createButton(
            'saveButton',
            'fa fa-floppy-o',
            () => {
                fetch('/CustomerNote.php?action=updateNote',
                    {
                        method: 'POST',
                        headers: {
                            dataType: 'application/json'
                        },
                        body: JSON.stringify(this.state.currentNote)
                    }
                )
                    .then(response => response.json())
                    .then(response => {
                        if (this.state.isAddingNote) {
                            this.setState(
                                {
                                    customerNotes: [response.data, ...this.state.customerNotes],
                                    currentNote: {...response.data},
                                    currentNoteIdx: 0,
                                    isAddingNote: false
                                }
                            )
                        } else {
                            this.setState({
                                    customerNotes: [...this.state.customerNotes.map(x => x.id === response.data.id ? response.data : x)],
                                    currentNote: {...response.data}
                                }
                            )
                        }
                    })
                    .catch(error => {
                        alert('Failed to save note');
                    })
            },
            ' Save'
        );
    }

    renderControls() {
        return this.el(
            'div',
            {
                key: 'controlsHolder',
                className: 'controlsHolder'
            },
            [
                this.getFirstButton(),
                this.getNextButton(),
                this.getPreviousButton(),
                this.getLastButton(),
                this.getDeleteButton(),
                this.getNewButton(),
                this.getSaveButton()
            ]
        )
    }

    renderNotes() {
        return this.el(
            React.Fragment,
            {
                key: 'notesHolder'
            },
            this.el(
                'div',
                {
                    style: {
                        display: 'flex',
                        "flex-direction": 'column',
                        height: "400px",
                        overflowY: 'scroll',
                        width: '740px'
                    }
                },
                [
                    ...this.state.customerNotes.map(x => {
                        return this.el(
                            'div',
                            {
                                key: `rowsHolder-${x.id}`,
                            },
                            [
                                this.el(
                                    'div',
                                    {
                                        key: `basicInfoRow-${x.id}`,
                                        style: {
                                            backgroundColor: '#cccccc'
                                        }
                                    },
                                    this.el(
                                        'span',
                                        {
                                            key: `date-${x.id}`,

                                        },
                                        [
                                            x.modifiedAt,
                                            ' - ',
                                            x.modifiedByName
                                        ]
                                    )
                                ),
                                this.el(
                                    'div',
                                    {
                                        key: `detailsRow-${x.id}`
                                    },
                                    x.note
                                )
                            ]
                        )
                    })
                ]
            )
        )
    }

    handleNoteTextChange($event) {
        console.log(this.state.currentNote);
        this.setState(
            {
                currentNote: {...this.state.currentNote, note: $event.target.value}
            });
    }

    renderNoteEditor() {
        return this.el(
            React.Fragment,
            {},
            [
                this.el(
                    'textarea',
                    {
                        cols: 120,
                        rows: 12,
                        value: this.state.currentNote && this.state.currentNote.note || '',
                        onChange: this.handleNoteTextChange
                    }
                )
            ]
        )
    }

    render() {
        if (!this.state.loaded) {
            return this.el(
                Skeleton,
                null,
                'Loading Data'
            );
        }


        return this.el(
            'div',
            {},
            [
                this.renderNotes(),
                this.renderControls(),
                this.renderNoteEditor()
            ]
        )
    }
}

export default CustomerNotesComponent;