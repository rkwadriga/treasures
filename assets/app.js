// assets/app.js
import { registerReactControllerComponents } from '@symfony/ux-react';
import './styles/app.css';
import './bootstrap';

registerReactControllerComponents(require.context('./react/controllers', true, /\.([jt])sx?$/));