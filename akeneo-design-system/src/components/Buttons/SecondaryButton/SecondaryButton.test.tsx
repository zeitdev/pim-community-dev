import React from 'react';
import 'jest-styled-components';
import {SecondaryButton} from './SecondaryButton';
import {render} from '@testing-library/react';

test('should render the component', () => {
    // Given
    const myLabel = 'Click Here';
    // When
    const {container} = render(<SecondaryButton>{myLabel}</SecondaryButton>);
    // Then
    expect(container.firstChild).toMatchInlineSnapshot(`
        .c0 {
          border-radius: 16px;
          cursor: pointer;
          font-size: 13px;
          font-weight: 400;
          height: 32px;
          line-height: 32px;
          padding: 0 15px;
          text-transform: uppercase;
        }

        .c0:disabled {
          cursor: not-allowed;
        }

        .c1 {
          color: white;
          background-color: #5992c7;
        }

        .c1:hover {
          background-color: #47749f;
        }

        .c1:active {
          background-color: #355777;
        }

        .c1:focus {
          border-color: #5992c7;
        }

        .c1:disabled {
          background-color: #bdd3e9;
        }

        <button
          class="c0 c1"
          role="button"
        >
          Click Here
        </button>
    `);
});
