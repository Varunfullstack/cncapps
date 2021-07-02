import React from 'react';
import ToolTip from "../ToolTip";

export const FilesChangedType = {
    CLICK: 'click',
    DROP: 'drop'
}

export default class DragAndDropUploaderComponent extends React.PureComponent {

    dragCounter = 0;
    fileUploader;

    constructor(props) {
        super(props);
        this.fileUploader = new React.createRef();
        this.state = {
            dragging: false
        }
    }

    fakeUploaderClick() {
        this.fileUploader.current.click();
    }

    handleUploaderFilesChanged(e) {
        e.preventDefault();
        e.stopPropagation();
        this.filesChanged(e.target.files, FilesChangedType.CLICK)

    }


    filesChanged(files, type) {
        const {onFilesChanged} = this.props;
        if (onFilesChanged) {
            onFilesChanged(files, type);
        }
    }

    handleDragIn(e) {
        e.preventDefault()
        e.stopPropagation()
        this.dragCounter++;
        if (e.dataTransfer.items && e.dataTransfer.items.length > 0) {
            this.setState({dragging: true});
        }
    }

    handleDragOut(e) {

        e.preventDefault()
        e.stopPropagation()
        this.dragCounter--;
        if (this.dragCounter > 0) return;
        this.setState({dragging: false});
    }

    handleDrag(e) {
        e.preventDefault()
        e.stopPropagation()
    }

    handleDrop(e) {
        e.preventDefault()
        e.stopPropagation()
        this.setState({dragging: false});
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            this.filesChanged(e.dataTransfer.files, FilesChangedType.DROP);
            this.dragCounter = 0;
            e.dataTransfer.clearData();
        }
    }

    render() {
        const {children,iconStyle} = this.props;
        const {dragging} = this.state;
        return (
            <div
                onDragEnter={(e) => this.handleDragIn(e)}
                onDragLeave={(e) => this.handleDragOut(e)}
                onDragOver={(e) => this.handleDrag(e)}
                onDrop={(e) => this.handleDrop(e)}
            >
                {dragging &&
                <div style={{
                    border: 'dashed grey 4px',
                    backgroundColor: 'rgba(255,255,255,.8)',
                    position: 'absolute',
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0,
                    zIndex: 9999
                }}
                >
                    <div style={{
                        position: 'absolute',
                        top: '50%',
                        right: 0,
                        left: 0,
                        textAlign: 'center',
                        color: 'grey',
                        fontSize: 36,
                        transform: "translateY(-50%)",
                    }}
                    >
                        <div>Drop Here</div>
                    </div>
                </div>
                }
                <div style={{width: 20}}>

                    <ToolTip title="Add document">
                        <i className="fal fa-plus pointer icon font-size-4"
                        style={iconStyle}
                           onClick={() => this.fakeUploaderClick()}
                        />
                    </ToolTip>

                    <input ref={this.fileUploader}
                           name='usefile'
                           type="file"
                           style={{display: "none"}}
                           multiple="multiple"
                           onChange={(e) => this.handleUploaderFilesChanged(e)}
                    />
                </div>
                {children}
            </div>
        )
    }
}





