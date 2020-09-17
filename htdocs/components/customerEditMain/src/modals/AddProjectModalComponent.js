import React from "react";
import {ModalBody, ModalDialog} from "react-bootstrap";
import ModalHeader from "react-bootstrap/ModalHeader";

const AddProjectModalComponent = () => {
    return (
        <ModalDialog>
            <ModalHeader closeButton>
                <h5 className="modal-title">
                    Add Project
                </h5>
            </ModalHeader>
            <ModalBody>
                <div className="row">
                    <div className="col-6">
                        <div className="form-group">
                            <label htmlFor="description">Description</label>
                            <input id="description"
                                   type="text"
                                   className="form-control input-sm"
                            />
                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-12">
                        <div className="pw-wisywig">
                            <div id="alerts"/>
                            <div className="btn-toolbar editor"
                                 data-role="editor-toolbar"
                                 data-target="#editor-one"
                            >
                                <div className="btn-group">
                                    <a className="btn dropdown-toggle"
                                       data-toggle="dropdown"
                                       title="Font"
                                    ><i className="fal fa-font"/>
                                        <b className="caret"/>
                                    </a>
                                    <ul className="dropdown-menu">
                                    </ul>
                                </div>
                                <div className="btn-group">
                                    <a className="btn dropdown-toggle"
                                       data-toggle="dropdown"
                                       title="Font Size"
                                    >
                                        <i className="fal fa-text-height"/>&nbsp;<b className="caret"/>
                                    </a>
                                    <ul className="dropdown-menu">
                                        <li>
                                            <a data-edit="fontSize 5">
                                                <p style="font-size:17px">Huge</p>
                                            </a>
                                        </li>
                                        <li>
                                            <a data-edit="fontSize 3">
                                                <p style="font-size:14px">Normal</p>
                                            </a>
                                        </li>
                                        <li>
                                            <a data-edit="fontSize 1">
                                                <p style="font-size:11px">Small</p>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div className="btn-group">
                                    <a className="btn"
                                       data-edit="bold"
                                       title="Bold (Ctrl/Cmd+B)"
                                    >
                                        <i className="fal fa-bold"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="italic"
                                       title="Italic (Ctrl/Cmd+I)"
                                    >
                                        <i className="fal fa-italic"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="strikethrough"
                                       title="Strikethrough"
                                    >
                                        <i className="fal fa-strikethrough"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="underline"
                                       title="Underline (Ctrl/Cmd+U)"
                                    >
                                        <i className="fal fa-underline"/>
                                    </a>
                                </div>
                                <div className="btn-group">
                                    <a className="btn"
                                       data-edit="insertunorderedlist"
                                       title="Bullet list"
                                    >
                                        <i className="fal fa-list-ul"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="insertorderedlist"
                                       title="Number list"
                                    >
                                        <i className="fal fa-list-ol"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="outdent"
                                       title="Reduce indent (Shift+Tab)"
                                    >
                                        <i className="fal fa-dedent"/>
                                    </a>
                                    <a className="btn"
                                       data-edit="indent"
                                       title="Indent (Tab)"
                                    ><i className="fal fa-indent"></i></a>
                                </div>
                                <div className="btn-group">
                                    <a className="btn btn-primary"
                                       data-edit="justifyleft"
                                       title="Align Left (Ctrl/Cmd+L)"
                                    ><i className="fal fa-align-left"></i></a>
                                    <a className="btn"
                                       data-edit="justifycenter"
                                       title="Center (Ctrl/Cmd+E)"
                                    ><i className="fal fa-align-center"></i></a>
                                    <a className="btn"
                                       data-edit="justifyright"
                                       title="Align Right (Ctrl/Cmd+R)"
                                    ><i className="fal fa-align-right"></i></a>
                                    <a className="btn"
                                       data-edit="justifyfull"
                                       title="Justify (Ctrl/Cmd+J)"
                                    ><i className="fal fa-align-justify"></i></a>
                                </div>
                                <div className="btn-group">
                                    <a className="btn dropdown-toggle"
                                       data-toggle="dropdown"
                                       title="Hyperlink"
                                    ><i className="fal fa-link"></i></a>
                                    <div className="dropdown-menu input-append">
                                        <input className="span2"
                                               placeholder="URL"
                                               type="text"
                                               data-edit="createLink"
                                        />
                                        <button className="btn"
                                                type="button"
                                        >Add
                                        </button>
                                    </div>
                                    <a className="btn"
                                       data-edit="unlink"
                                       title="Remove Hyperlink"
                                    ><i className="fal fa-cut"></i></a>
                                </div>
                                <div className="btn-group">
                                    <a className="btn"
                                       title="Insert picture (or just drag &amp; drop)"
                                       id="pictureBtn"
                                    ><i
                                        className="fal fa-picture-o"
                                    ></i></a>
                                    <input type="file"
                                           data-role="magic-overlay"
                                           data-target="#pictureBtn"
                                           data-edit="insertImage"
                                    >
                                </div>
                                <div className="btn-group">
                                    <a className="btn"
                                       data-edit="undo"
                                       title="Undo (Ctrl/Cmd+Z)"
                                    ><i className="fal fa-undo"></i></a>
                                    <a className="btn"
                                       data-edit="redo"
                                       title="Redo (Ctrl/Cmd+Y)"
                                    ><i className="fal fa-repeat"></i></a>
                                </div>
                            </div>
                            <div id="editor-one"
                                 className="editor-wrapper placeholderText"
                                 contentEditable="true"
                            ></div>
                            <textarea name="descr"
                                      id="descr"
                                      style="display:none;"
                            ></textarea>
                        </div>
                    </div>
                </div>
                <div className="row">
                    <div className="col-3">
                        <div className="form-group">
                            <label htmlFor="projctOpenedDate">Project Opened Date</label>
                            <input id="projctOpenedDate"
                                   type="text"
                                   className="form-control input-sm"
                            >
                        </div>
                    </div>
                    <div className="col-3">
                        <div className="form-group">
                            <label htmlFor="completedDate">Completed Date</label>
                            <input id="completedDate"
                                   type="text"
                                   className="form-control input-sm"
                            >
                        </div>
                    </div>
                    <div className="col-3">
                        <div className="form-group">
                            <label htmlFor="completedDate">Completed Date</label>
                            <input id="completedDate"
                                   type="text"
                                   className="form-control input-sm"
                            >
                        </div>
                    </div>
                    <div className="col-3">
                        <div className="form-group">
                            <label htmlFor="projectEngineer">Project Engineer</label>
                            <select name=""
                                    id="projectEngineer"
                                    className="form-control input-sm"
                            >
                                <option value=""></option>
                            </select>
                        </div>
                    </div>
                    <div className="col-6">
                        <div className="form-group">
                            <label htmlFor="addUpdate">Add Update</label>
                            <input id="addUpdate"
                                   type="text"
                                   className="form-control input-sm"
                            >
                        </div>
                    </div>
                    <div className="col-6">
                        <div className="form-group">
                            <a className="btn"
                               title="Insert picture (or just drag &amp; drop)"
                               id="pictureBtn2"
                            ><i className="fal fa-picture-o"></i></a>
                            <input type="file"
                                   data-role="magic-overlay"
                                   data-target="#pictureBtn2"
                                   data-edit="insertImage"
                            >
                        </div>
                    </div>
                    <div className="col-6">
                        <div className="form-group">
                            <label htmlFor="">Last Update By:&nbsp;<span className="font-weight-normal">John Doe</span></label>
                            <a className="d-block"
                               href=""
                            >See Previous History</a>
                        </div>
                    </div>
                </div>
            </ModalBody>
        </ModalDialog>
)
}