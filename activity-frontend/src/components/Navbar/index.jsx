import './style.css';
import { Link } from 'react-router-dom';

const Navbar = () => {
    return (
        <nav>
            <ul>
                <li>
                    <Link to='/'>Upload</Link>
                </li>
                <li>
                    <Link to='/dashboard'>Dasboard</Link>
                </li>
                <li>
                    <Link to='/analytics'>Analytics</Link>
                </li>
            </ul>
        </nav>
    );
};

export default Navbar;