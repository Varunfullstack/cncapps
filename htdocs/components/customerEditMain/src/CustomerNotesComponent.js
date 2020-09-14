import React, {Fragment} from 'react';
import Skeleton from "react-loading-skeleton";

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
        return (
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
        this.setState(
            {
                currentNote: {...this.state.currentNote, note: $event.target.value}
            });
    }

    render() {
        if (!this.state.loaded) {
            return this.el(
                Skeleton,
                null,
                'Loading Data'
            );
        }


        return (
            <Fragment>
                <div className="form-group customerNoteHistory">
                    {this.renderNotes()}
                    <div className="customerNoteNav mt-3 mb-3">
                        <button type="button"
                                name="First"
                                aria-hidden="true"
                                onClick={() => {
                                    const nextIdx = 0;
                                    this.changeNote(nextIdx);
                                }}
                                className="btn btn-outline-secondary"
                        >
                            <i className="fa fa-step-backward">
                            </i> First
                        </button>
                        <button type="button"
                                name="Previous"
                                onClick={
                                    () => {
                                        const nextIdx = this.state.currentNoteIdx - 1;
                                        this.changeNote(nextIdx);
                                    }
                                }
                                className="btn btn-outline-secondary"
                        >
                            <i className="fa fa-backward"
                               aria-hidden="true"
                            >
                            </i> Back
                        </button>
                        <button type="button"
                                name="Next"
                                onClick={
                                    () => {
                                        const nextIdx = this.state.currentNoteIdx + 1;
                                        this.changeNote(nextIdx);
                                    }
                                }
                                className="btn btn-outline-secondary"
                        >
                            Next
                            <i className="fa fa-forward"
                               aria-hidden="true"
                            />
                        </button>

                        <button type="button"
                                name="Last"
                                onClick={
                                    () => {
                                        const nextIdx = this.state.customerNotes.length - 1;
                                        this.changeNote(nextIdx);
                                    }
                                }
                                className="btn btn-outline-secondary"
                        >
                            Last
                            <i className="fa fa-step-forward"
                               aria-hidden="true"
                            />
                        </button>
                        {

                        }
                        <button type="button"
                                name="Delete"
                                onClick={
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
                                    }
                                }
                                className="btn btn-outline-danger"
                        >
                            <i className="fa fa-trash"
                               aria-hidden="true"
                            />
                            Delete
                        </button>
                        <button type="button"
                                name="New"
                                onClick={
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
                                    }
                                }
                                className="btn btn-outline-secondary"
                        >
                            <i className="fa fa-plus-circle"
                               aria-hidden="true"
                            />
                            New
                        </button>
                        <button type="button"
                                name="Save"
                                onClick={
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
                                    }
                                }
                                className="btn btn-outline-secondary"
                        >
                            <i className="fa fa-floppy-o"
                               aria-hidden="true"
                            />

                            Save
                        </button>
                    </div>
                </div>
                <div className="form-group customerNoteDetails">
                    <textarea name="customerNoteDetails"
                              id="customerNoteDetails"
                              cols="120"
                              value={this.state.currentNote && this.state.currentNote.note || ''}
                              rows="12"
                              className="form-control"
                              onChange={($event) => this.handleNoteTextChange($event)}
                    />
                </div>
            </Fragment>
        )

    }
}

export default CustomerNotesComponent;